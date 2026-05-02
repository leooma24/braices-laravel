<?php

namespace App\Services;

use App\Models\Pricing;
use App\Models\Property;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class ReservationPricingService
{
    /**
     * Cotiza una estadía. Aplica precio dinámico de la tabla `pricing`
     * cuando exista para una fecha específica; si no, cae al
     * `price_per_night` de la propiedad.
     *
     * Convención: la noche del check-out NO se cobra (se cuenta el rango
     * [check_in, check_out)). Esto sigue la convención estándar de booking.
     *
     * @return array{nights:int, subtotal:string, cleaning_fee:string, total:string, breakdown:array<int,array{date:string, price:string}>}
     */
    public function quote(Property $property, string $checkIn, string $checkOut): array
    {
        $start = CarbonImmutable::parse($checkIn)->startOfDay();
        $end = CarbonImmutable::parse($checkOut)->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('check_out_date debe ser posterior a check_in_date');
        }

        $nights = (int) $start->diffInDays($end);
        $defaultPrice = (float) ($property->price_per_night ?? 0);

        // Cargar precios dinámicos de una sola vez para todo el rango.
        $dynamic = Pricing::where('property_id', $property->id)
            ->whereBetween('date', [$start->toDateString(), $end->subDay()->toDateString()])
            ->get()
            ->keyBy(fn ($p) => CarbonImmutable::parse($p->date)->toDateString());

        $breakdown = [];
        $subtotal = 0.0;

        foreach (CarbonPeriod::create($start, '1 day', $end->subDay()) as $day) {
            $key = $day->toDateString();
            $price = isset($dynamic[$key]) ? (float) $dynamic[$key]->price_per_night : $defaultPrice;
            $subtotal += $price;
            $breakdown[] = [
                'date' => $key,
                'price' => number_format($price, 2, '.', ''),
            ];
        }

        $cleaningFee = (float) ($property->cleaning_fee ?? 0);
        $total = $subtotal + $cleaningFee;

        return [
            'nights' => $nights,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'cleaning_fee' => number_format($cleaningFee, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'breakdown' => $breakdown,
        ];
    }
}
