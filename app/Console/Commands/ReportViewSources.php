<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportViewSources extends Command
{
    protected $signature = 'views:sources {--days=7 : Cuántos días hacia atrás sumar} {--property= : Filtrar por id de propiedad}';

    protected $description = 'Muestra de dónde llegaron las visitas a las fichas: Facebook, Google, directo o navegación interna.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $since = now()->startOfDay()->subDays($days)->toDateString();

        $rows = DB::table('property_view_sources as s')
            ->join('properties as p', 'p.id', '=', 's.property_id')
            ->where('s.day', '>=', $since)
            ->when($this->option('property'), fn ($q, $id) => $q->where('s.property_id', $id))
            ->groupBy('s.property_id', 'p.title', 's.source')
            ->selectRaw('p.title, s.source, sum(s.views) as total')
            ->orderByDesc('total')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn("Todavía no hay visitas registradas con origen en los últimos {$days} días.");
            $this->line('Recuerda que solo cuentan las visitas posteriores al despliegue de esta función.');

            return self::SUCCESS;
        }

        $this->info("Origen de las visitas, últimos {$days} días");
        $this->newLine();

        $this->table(
            ['Origen', 'Visitas'],
            $rows->groupBy('source')
                ->map(fn ($group, $source) => [$source, (int) $group->sum('total')])
                ->sortByDesc(fn ($row) => $row[1])
                ->values()
                ->all()
        );

        $this->newLine();
        $this->info('Por propiedad');

        $this->table(
            ['Propiedad', 'Origen', 'Visitas'],
            $rows->map(fn ($r) => [
                mb_strimwidth($r->title ?? '(sin título)', 0, 44, '…'),
                $r->source,
                (int) $r->total,
            ])->all()
        );

        return self::SUCCESS;
    }
}
