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
     * Igual que forProperty pero para varias de un jalón: una sola consulta de
     * fotos en vez de dos por propiedad, que es lo que haría el panel del dueño
     * si se llamara en un bucle.
     *
     * @param  \Illuminate\Support\Collection<int, Property>  $properties
     * @return array<int, array{views: int, days: int}> indexado por property_id
     */
    public function forProperties($properties, int $days = 30): array
    {
        $ids = $properties->pluck('id');
        if ($ids->isEmpty()) {
            return [];
        }

        $since = now()->startOfDay()->subDays($days)->toDateString();

        $snapshots = PropertyViewSnapshot::query()
            ->whereIn('property_id', $ids)
            ->orderBy('captured_on')
            ->get()
            ->groupBy('property_id');

        $out = [];

        foreach ($properties as $property) {
            $history = $snapshots->get($property->id);
            if (! $history || $history->isEmpty()) {
                continue;
            }

            $baseline = $history->last(fn ($s) => $s->captured_on->toDateString() <= $since)
                ?? $history->first();

            $out[$property->id] = [
                'views' => max(0, (int) $property->views - $baseline->views),
                'days' => max(1, (int) $baseline->captured_on->startOfDay()->diffInDays(now()->startOfDay())),
            ];
        }

        return $out;
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
