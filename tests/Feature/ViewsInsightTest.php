<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyViewSnapshot;
use App\Models\User;
use App\Services\ViewsInsight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewsInsightTest extends TestCase
{
    use RefreshDatabase;

    private ViewsInsight $insight;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        \DB::table('property_status')->insert(['id' => 1, 'name' => 'Activa']);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Venta']);

        $this->insight = new ViewsInsight;
        $this->owner = User::create([
            'name' => 'owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('secret'),
            'slug' => 'owner',
        ]);
    }

    private function makeProperty(string $slug, int $views): Property
    {
        $property = new Property([
            'title' => 'Casa '.$slug,
            'description' => 'desc',
            'address' => 'addr',
            'city' => 'Los Mochis',
            'state' => 25,
            'country' => 1,
            'zip' => '81200',
            'price' => 1_000_000,
            'property_status_id' => 1,
            'transaction_type_id' => 1,
        ]);
        $property->user_id = $this->owner->id;
        $property->slug = $slug;
        $property->views = $views;
        $property->save();

        return $property;
    }

    public function test_calcula_las_visitas_nuevas_desde_la_foto_mas_vieja_del_periodo(): void
    {
        $property = $this->makeProperty('casa-uno', 261);

        PropertyViewSnapshot::create([
            'property_id' => $property->id,
            'views' => 100,
            'captured_on' => now()->subDays(7)->toDateString(),
        ]);

        $result = $this->insight->forProperty($property, 7);

        $this->assertSame(161, $result['views']);
        $this->assertSame(7, $result['days']);
    }

    public function test_sin_fotos_previas_devuelve_null(): void
    {
        $property = $this->makeProperty('casa-dos', 50);

        $this->assertNull($this->insight->forProperty($property, 30));
    }

    public function test_reporta_el_tramo_real_cuando_no_alcanza_el_periodo_pedido(): void
    {
        $property = $this->makeProperty('casa-tres', 80);

        // Solo hay 4 dias guardados aunque se pidan 30.
        PropertyViewSnapshot::create([
            'property_id' => $property->id,
            'views' => 60,
            'captured_on' => now()->subDays(4)->toDateString(),
        ]);

        $result = $this->insight->forProperty($property, 30);

        $this->assertSame(20, $result['views']);
        $this->assertSame(4, $result['days'], 'No debe presumir 30 dias si solo midio 4.');
    }

    public function test_for_properties_resuelve_varias_en_una_sola_consulta(): void
    {
        $uno = $this->makeProperty('casa-a', 200);
        $dos = $this->makeProperty('casa-b', 90);
        $sinDatos = $this->makeProperty('casa-c', 10);

        foreach ([[$uno, 150], [$dos, 40]] as [$property, $views]) {
            PropertyViewSnapshot::create([
                'property_id' => $property->id,
                'views' => $views,
                'captured_on' => now()->subDays(5)->toDateString(),
            ]);
        }

        $queries = 0;
        \DB::listen(function () use (&$queries) {
            $queries++;
        });

        $result = $this->insight->forProperties(collect([$uno, $dos, $sinDatos]), 5);

        $this->assertSame(1, $queries, 'Debe ser una sola consulta, no una por propiedad.');
        $this->assertSame(50, $result[$uno->id]['views']);
        $this->assertSame(50, $result[$dos->id]['views']);
        $this->assertArrayNotHasKey($sinDatos->id, $result);
    }

    public function test_el_resumen_del_sitio_ignora_las_propiedades_sin_medicion(): void
    {
        $uno = $this->makeProperty('casa-x', 300);
        $this->makeProperty('casa-y', 500);

        PropertyViewSnapshot::create([
            'property_id' => $uno->id,
            'views' => 250,
            'captured_on' => now()->subDays(6)->toDateString(),
        ]);

        $summary = $this->insight->siteSummary(30);

        $this->assertSame(1, $summary['properties']);
        $this->assertSame(50, $summary['total']);
        $this->assertSame(50, $summary['average']);
        $this->assertSame(50, $summary['best']);
        $this->assertSame(6, $summary['days']);
    }
}
