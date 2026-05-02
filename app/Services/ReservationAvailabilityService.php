<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\PropertyAvailability;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class ReservationAvailabilityService
{
    /**
     * Verifica si una propiedad está disponible para el rango [checkIn, checkOut).
     * Reglas:
     *   1. La propiedad debe ser reservable.
     *   2. Ninguna otra reservación bloqueante (pendiente/confirmada) se debe solapar.
     *   3. Ningún día del rango debe tener PropertyAvailability con is_available = false.
     *
     * @param  int|null  $ignoreReservationId  útil al reagendar una reservación existente.
     */
    public function isAvailable(Property $property, string $checkIn, string $checkOut, ?int $ignoreReservationId = null): bool
    {
        $start = CarbonImmutable::parse($checkIn)->startOfDay();
        $end = CarbonImmutable::parse($checkOut)->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('check_out_date debe ser posterior a check_in_date');
        }

        if (!$property->is_reservable) {
            return false;
        }

        $reservationOverlap = Reservation::query()
            ->forProperty($property->id)
            ->blocking()
            ->overlapping($start->toDateString(), $end->toDateString())
            ->when($ignoreReservationId, fn ($q) => $q->where('id', '!=', $ignoreReservationId))
            ->exists();

        if ($reservationOverlap) {
            return false;
        }

        // El último día (check_out) no se ocupa, por eso $end->subDay().
        $blockedDay = PropertyAvailability::query()
            ->where('property_id', $property->id)
            ->whereBetween('date', [$start->toDateString(), $end->subDay()->toDateString()])
            ->where('is_available', false)
            ->exists();

        return !$blockedDay;
    }

    /**
     * Devuelve un array de fechas (YYYY-MM-DD) bloqueadas dentro del rango.
     * Útil para pintar el calendario en el frontend.
     *
     * @return array<int,string>
     */
    public function blockedDates(Property $property, string $rangeStart, string $rangeEnd): array
    {
        $start = CarbonImmutable::parse($rangeStart)->startOfDay();
        $end = CarbonImmutable::parse($rangeEnd)->startOfDay();

        if ($end->lessThan($start)) {
            return [];
        }

        // Días marcados explícitamente como no disponibles.
        $manualBlocks = PropertyAvailability::query()
            ->where('property_id', $property->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('is_available', false)
            ->pluck('date')
            ->map(fn ($d) => CarbonImmutable::parse($d)->toDateString())
            ->all();

        // Días dentro de reservaciones bloqueantes que cruzan el rango.
        $reservationBlocks = [];
        $reservations = Reservation::query()
            ->forProperty($property->id)
            ->blocking()
            ->where('check_in_date', '<', $end->addDay()->toDateString())
            ->where('check_out_date', '>', $start->toDateString())
            ->get(['check_in_date', 'check_out_date']);

        foreach ($reservations as $reservation) {
            $resStart = CarbonImmutable::parse($reservation->check_in_date);
            $resEnd = CarbonImmutable::parse($reservation->check_out_date)->subDay();
            foreach (CarbonPeriod::create($resStart, '1 day', $resEnd) as $day) {
                if ($day->between($start, $end)) {
                    $reservationBlocks[] = $day->toDateString();
                }
            }
        }

        return array_values(array_unique([...$manualBlocks, ...$reservationBlocks]));
    }

    /**
     * Marca un rango de fechas como no disponible (bloqueo manual del anfitrión).
     */
    public function block(Property $property, string $from, string $to): void
    {
        $this->setAvailability($property, $from, $to, false);
    }

    /**
     * Libera un rango de fechas (vuelve a estar disponible).
     */
    public function unblock(Property $property, string $from, string $to): void
    {
        $this->setAvailability($property, $from, $to, true);
    }

    private function setAvailability(Property $property, string $from, string $to, bool $available): void
    {
        $start = CarbonImmutable::parse($from)->startOfDay();
        $end = CarbonImmutable::parse($to)->startOfDay();

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            PropertyAvailability::updateOrCreate(
                ['property_id' => $property->id, 'date' => $day->toDateString()],
                ['is_available' => $available],
            );
        }
    }
}
