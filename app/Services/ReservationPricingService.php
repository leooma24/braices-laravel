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
     * `price_per_night` de la propiedad (que para las propiedades con
     * `rate_period = 'mes'` guarda la renta mensual, no la de una noche).
     *
     * Convención por noche: la noche del check-out NO se cobra (se cuenta el
     * rango [check_in, check_out)). Esto sigue la convención estándar de booking.
     *
     * Convención por mes: se cobran meses completos y cualquier fracción
     * sube al siguiente mes (del 15 de enero al 20 de febrero = 2 meses),
     * con un mínimo de 1. Los precios dinámicos por día no aplican aquí.
     *
     * @return array{nights:int, units:int, period:string, subtotal:string, cleaning_fee:string, total:string, breakdown:array<int,array{date:string, price:string}>}
     */
    public function quote(Property $property, string $checkIn, string $checkOut): array
    {
        $start = CarbonImmutable::parse($checkIn)->startOfDay();
        $end = CarbonImmutable::parse($checkOut)->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('check_out_date debe ser posterior a check_in_date');
        }

        $defaultPrice = (float) ($property->price_per_night ?? 0);
        $cleaningFee = (float) ($property->cleaning_fee ?? 0);

        if ($property->isMonthlyRate()) {
            [$units, $subtotal, $breakdown] = $this->quoteMonthly($start, $end, $defaultPrice);
        } else {
            [$units, $subtotal, $breakdown] = $this->quoteNightly($property, $start, $end, $defaultPrice);
        }

        $total = $subtotal + $cleaningFee;

        return [
            // `nights` se conserva por compatibilidad: es el número de
            // unidades cobradas, noches o meses según el periodo.
            'nights' => $units,
            'units' => $units,
            'period' => $property->ratePeriod(),
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'cleaning_fee' => number_format($cleaningFee, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * @return array{0:int, 1:float, 2:array<int,array{date:string, price:string}>}
     */
    private function quoteNightly(Property $property, CarbonImmutable $start, CarbonImmutable $end, float $defaultPrice): array
    {
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

        return [(int) $start->diffInDays($end), $subtotal, $breakdown];
    }

    /**
     * @return array{0:int, 1:float, 2:array<int,array{date:string, price:string}>}
     */
    private function quoteMonthly(CarbonImmutable $start, CarbonImmutable $end, float $monthlyPrice): array
    {
        $months = (int) $start->diffInMonths($end);
        if ($start->addMonths($months)->lessThan($end)) {
            $months++;
        }
        $months = max(1, $months);

        $breakdown = [];
        for ($i = 0; $i < $months; $i++) {
            $breakdown[] = [
                'date' => $start->addMonths($i)->toDateString(),
                'price' => number_format($monthlyPrice, 2, '.', ''),
            ];
        }

        return [$months, $months * $monthlyPrice, $breakdown];
    }
}
