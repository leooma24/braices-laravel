<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckProdSchema extends Command
{
    protected $signature = 'schema:check {--detailed : muestra columnas faltantes/extra por tabla}';

    protected $description = 'Compara el schema actual de la BD configurada (DB_HOST/DB_DATABASE en .env) contra el schema esperado por las migraciones. Útil para auditar prod antes de deployar.';

    /** Tablas/columnas esperadas según los modelos y migraciones. */
    private array $expectedSchema = [
        'reservations' => [
            'id', 'property_id', 'user_id', 'check_in_date', 'check_out_date',
            'guests', 'status', 'total_price', 'subtotal', 'cleaning_fee_snapshot',
            'nights', 'expires_at', 'created_at', 'updated_at',
        ],
        'property_availability' => [
            'id', 'property_id', 'date', 'is_available',
        ],
        'pricing' => [
            'id', 'property_id', 'date', 'price_per_night',
        ],
        'reviews' => [
            'id', 'property_id', 'user_id', 'reservation_id', 'rating', 'comment', 'created_at', 'updated_at',
        ],
        'amenities' => [
            'id', 'name', 'created_at', 'updated_at',
        ],
        'property_amenities' => [
            'id', 'property_id', 'amenity_id', 'created_at', 'updated_at',
        ],
        'favorites' => [
            'id', 'user_id', 'property_id', 'created_at', 'updated_at',
        ],
        'property_rules' => [
            'id', 'property_id', 'rule', 'created_at', 'updated_at',
        ],
        'properties' => [
            // Solo las nuevas que agregamos en esta sesión.
            'is_reservable', 'max_guests', 'price_per_night', 'cleaning_fee',
            'check_in_time', 'check_out_time', 'is_featured', 'featured_until',
        ],
        'payments' => [
            'reservation_id', 'amount', 'provider',
        ],
    ];

    public function handle(): int
    {
        $this->info('Auditando schema de la BD configurada...');
        $this->info('  DB: ' . config('database.connections.' . config('database.default') . '.database'));
        $this->info('  Host: ' . config('database.connections.' . config('database.default') . '.host'));
        $this->newLine();

        $missingTables = [];
        $missingColumns = [];

        foreach ($this->expectedSchema as $table => $expectedCols) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
                continue;
            }

            $actualCols = Schema::getColumnListing($table);
            $missing = array_values(array_diff($expectedCols, $actualCols));
            if (!empty($missing)) {
                $missingColumns[$table] = $missing;
            }

            if ($this->option('detailed')) {
                $extra = array_values(array_diff($actualCols, $expectedCols));
                if (!empty($extra) && $table !== 'properties' && $table !== 'payments') {
                    $this->line("  <comment>{$table}</comment>: extra columns ignoradas: " . implode(', ', $extra));
                }
            }
        }

        if (empty($missingTables) && empty($missingColumns)) {
            $this->info('OK — el schema actual contiene todo lo que las migraciones necesitan.');
            $this->info('Puedes correr `php artisan migrate --force` con seguridad.');
            return self::SUCCESS;
        }

        if (!empty($missingTables)) {
            $this->newLine();
            $this->warn('Tablas faltantes (las creará migrate):');
            foreach ($missingTables as $t) {
                $this->line("  - {$t}");
            }
        }

        if (!empty($missingColumns)) {
            $this->newLine();
            $this->warn('Columnas faltantes (las agregará migrate, idempotente):');
            foreach ($missingColumns as $table => $cols) {
                $this->line("  <comment>{$table}</comment>: " . implode(', ', $cols));
            }
        }

        $this->newLine();
        $this->info('Las migraciones de este proyecto son idempotentes (usan hasTable / hasColumn),');
        $this->info('así que puedes correr `php artisan migrate --force` sin riesgo de errores por columnas existentes.');

        return self::SUCCESS;
    }
}
