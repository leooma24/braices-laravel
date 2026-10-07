<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyViewSnapshot;

/**
 * Lee las fotos diarias de `property_view_snapshots` y las convierte en cifras
 * presentables: cuántas visitas ganó una propiedad en un periodo y cómo le fue
 * al catálogo completo.
 *
 * Importante: solo informa sobre el periodo que realmente tiene fotos. Si se
 * piden 30 días y solo hay 6 guardados, devuelve 6 y lo dice en `days`, para no
 * presumir un número que no se midió.
 */
class ViewsInsight
{
    /**
     * Visitas nuevas de una propiedad y el tramo real medido.
     *
     * @return array{views: int, days: int}|null null si aún no hay foto previa
     */
    public function forProperty(Property $property, int $days = 30): ?array
    {
        $since = now()->startOfDay()->subDays($days)->toDateString();

        $baseline = PropertyViewSnapshot::query()
            ->where('property_id', $property->id)
            ->where('captured_on', '<=', $since)
            ->orderByDesc('captured_on')
            ->first()
            ?? PropertyViewSnapshot::query()
                ->where('property_id', $property->id)
                ->orderBy('captured_on')
                ->first();

        if (! $baseline) {
            return null;
        }

        $span = $baseline->captured_on->startOfDay()->diffInDays(now()->startOfDay());

        return [
            'views' => max(0, (int) $property->views - $baseline->views),
            'days' => max(1, (int) $span),
        ];
    }

    /**
     * Resumen del catálogo activo para mostrarle a un dueño que está decidiendo
     * dónde publicar.
     *
     * @return array{days: int, properties: int, total: int, average: int, best: int}
     */
    public function siteSummary(int $days = 30): array
    {
        $properties = Property::query()
            ->where('property_status_id', 1)
            ->get(['id', 'views']);

        $total = 0;
        $counted = 0;
        $best = 0;
        $span = 0;

        foreach ($properties as $property) {
            $result = $this->forProperty($property, $days);
            if (! $result) {
                continue;
            }

            $total += $result['views'];
            $best = max($best, $result['views']);
            $span = max($span, $result['days']);
            $counted++;
        }

        return [
            'days' => max(1, $span),
            'properties' => $counted,
            'total' => $total,
            'average' => $counted > 0 ? (int) round($total / $counted) : 0,
            'best' => $best,
        ];
    }
}
