<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyViewSnapshot;
use Illuminate\Console\Command;

class ReportPropertyViews extends Command
{
    protected $signature = 'views:report {--days=7 : Cuántos días hacia atrás comparar}';

    protected $description = 'Muestra cuántas vistas nuevas ganó cada propiedad en los últimos días.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $since = now()->startOfDay()->subDays($days)->toDateString();

        $properties = Property::query()->select('id', 'title', 'views')->get();
        if ($properties->isEmpty()) {
            $this->warn('No hay propiedades.');

            return self::SUCCESS;
        }

        // Para cada propiedad, la foto más reciente que sea de hace >= $days.
        // Si no hay tan vieja, se usa la más vieja disponible y se reporta el
        // periodo real, para no inventar un "por día" sobre días sin datos.
        $snapshots = PropertyViewSnapshot::query()
            ->whereIn('property_id', $properties->pluck('id'))
            ->orderBy('captured_on')
            ->get()
            ->groupBy('property_id');

        if ($snapshots->isEmpty()) {
            $this->warn('Todavía no hay fotos guardadas. Corre `php artisan views:snapshot` y vuelve en unos días.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($properties as $property) {
            $history = $snapshots->get($property->id);
            if (! $history || $history->isEmpty()) {
                continue;
            }

            $baseline = $history->last(fn ($s) => $s->captured_on->toDateString() <= $since)
                ?? $history->first();

            $span = max(1, $baseline->captured_on->startOfDay()->diffInDays(now()->startOfDay()));
            $delta = (int) $property->views - $baseline->views;

            $rows[] = [
                'title' => mb_strimwidth($property->title ?? '(sin título)', 0, 46, '…'),
                'total' => number_format($property->views),
                'delta' => $delta,
                'span' => $span,
                'per_day' => round($delta / $span, 1),
            ];
        }

        if ($rows === []) {
            $this->warn('Ninguna propiedad tiene fotos previas todavía.');

            return self::SUCCESS;
        }

        usort($rows, fn ($a, $b) => $b['delta'] <=> $a['delta']);

        $this->table(
            ['Propiedad', 'Vistas hoy', 'Nuevas', 'Días', 'Por día'],
            array_map(fn ($r) => [$r['title'], $r['total'], '+'.$r['delta'], $r['span'], $r['per_day']], $rows)
        );

        return self::SUCCESS;
    }
}
