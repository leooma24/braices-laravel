<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyViewSnapshot;
use Illuminate\Console\Command;

class SnapshotPropertyViews extends Command
{
    protected $signature = 'views:snapshot {--date= : Fecha de la foto en formato Y-m-d (por defecto hoy)}';

    protected $description = 'Guarda el contador de vistas de cada propiedad para poder medir el movimiento por periodo.';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->toDateString();

        $rows = Property::query()
            ->select('id', 'views')
            ->get()
            ->map(fn ($p) => [
                'property_id' => $p->id,
                'views' => (int) $p->views,
                'captured_on' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows === []) {
            $this->warn('No hay propiedades que fotografiar.');

            return self::SUCCESS;
        }

        // Si el comando corre dos veces el mismo día, la última gana.
        PropertyViewSnapshot::upsert($rows, ['property_id', 'captured_on'], ['views', 'updated_at']);

        $this->info('Foto de vistas guardada para '.count($rows)." propiedades ({$date}).");

        return self::SUCCESS;
    }
}
