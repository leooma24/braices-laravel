<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyViewSnapshot;
use Illuminate\Database\Seeder;

class PropertyViewBaselineSeeder extends Seeder
{
    /**
     * Baseline del 2026-10-01: los contadores de vistas leídos de las fichas
     * públicas de bienescorp.com ese día, antes de que existiera la foto
     * diaria. Sirve para que el primer `views:report` ya tenga con qué
     * comparar en vez de esperar una semana.
     *
     * Solo inserta donde no haya foto previa de esa fecha — correrlo dos veces
     * no pisa nada.
     */
    private const CAPTURED_ON = '2026-10-01';

    private const VIEWS = [
        'casa-de-2-plantas-en-residencial-el-pueblito-los-mochis' => 261,
        'departamento-todo-incluido' => 79,
        'compra-venta-con-posesion-2500000' => 1319,
        'casa-toledo-corro' => 1887,
        'casa-col-centro-excelente-oportunidad' => 1581,
        'especial-para-negocio-comercial' => 999,
        'local-comercial-en-juan-jose-rios-1000m2' => 1519,
        'terreno-en-camahuiroa-frente-al-mar' => 1375,
        'terreno-de-compra-venta-con-posesion' => 1674,
        'excelente-oportunidad-ej-mexico' => 996,
        'excelente-terreno-en-zona-industrial' => 1082,
        'terreno-en-venta' => 960,
        'casa-de-oportunidad-en-praderas-de-villa' => 1281,
    ];

    public function run(): void
    {
        $ids = Property::whereIn('slug', array_keys(self::VIEWS))->pluck('id', 'slug');

        foreach (self::VIEWS as $slug => $views) {
            if (! isset($ids[$slug])) {
                $this->command?->warn("Sin propiedad para el slug {$slug}; se omite.");

                continue;
            }

            PropertyViewSnapshot::firstOrCreate(
                ['property_id' => $ids[$slug], 'captured_on' => self::CAPTURED_ON],
                ['views' => $views]
            );
        }

        $this->command?->info('Baseline de vistas del '.self::CAPTURED_ON.' cargado.');
    }
}
