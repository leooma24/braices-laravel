<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Services\VisitSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class VisitSourceTest extends TestCase
{
    use RefreshDatabase;

    private VisitSource $source;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        \DB::table('property_status')->insert(['id' => 1, 'name' => 'Activa']);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Venta']);

        $this->source = new VisitSource;

        $user = User::create([
            'name' => 'owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('secret'),
            'slug' => 'owner',
        ]);

        $this->property = new Property([
            'title' => 'Casa',
            'description' => 'desc',
            'address' => 'addr',
            'city' => 'Los Mochis',
            'state' => 25,
            'country' => 1,
            'zip' => '81200',
            'price' => 1000,
            'property_status_id' => 1,
            'transaction_type_id' => 1,
        ]);
        $this->property->user_id = $user->id;
        $this->property->slug = 'casa';
        $this->property->save();
    }

    private function request(array $query = [], array $headers = [], string $agent = 'Mozilla/5.0 (iPhone)'): Request
    {
        $request = Request::create('/propiedad/casa', 'GET', $query);
        $request->headers->set('User-Agent', $agent);
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        return $request;
    }

    public function test_la_marca_manual_gana_sobre_todo(): void
    {
        $detected = $this->source->detect($this->request(
            ['de' => 'facebook'],
            ['referer' => 'https://www.google.com/search?q=casas']
        ));

        $this->assertSame('facebook', $detected);
    }

    public function test_reconoce_el_referer_de_facebook(): void
    {
        $this->assertSame('facebook', $this->source->detect(
            $this->request([], ['referer' => 'https://l.facebook.com/'])
        ));
    }

    public function test_sin_referer_es_directo(): void
    {
        $this->assertSame('directo', $this->source->detect($this->request()));
    }

    public function test_la_navegacion_dentro_del_sitio_no_se_confunde_con_trafico_nuevo(): void
    {
        $this->assertSame('interno', $this->source->detect(
            $this->request([], ['referer' => 'https://bienescorp.com/propiedades'])
        ));
    }

    public function test_limpia_una_marca_con_caracteres_peligrosos(): void
    {
        // El parametro viene de la URL, asi que lo escribe cualquiera. Se
        // quedan solo letras, numeros, guion y guion bajo.
        $detected = $this->source->detect(
            $this->request(['de' => 'Facebook<script>/Grupos'])
        );

        $this->assertSame('facebookscriptgrupos', $detected);
        $this->assertMatchesRegularExpression('/^[a-z0-9_-]+$/', $detected);
    }

    public function test_recorta_una_marca_absurdamente_larga(): void
    {
        $detected = $this->source->detect(
            $this->request(['de' => str_repeat('a', 200)])
        );

        $this->assertSame(30, strlen($detected), 'No debe exceder el ancho de la columna.');
    }

    public function test_suma_en_la_misma_fila_del_dia(): void
    {
        $request = $this->request(['de' => 'facebook']);

        $this->source->record($this->property, $request);
        $this->source->record($this->property, $request);
        $this->source->record($this->property, $request);

        $this->assertDatabaseCount('property_view_sources', 1);
        $this->assertDatabaseHas('property_view_sources', [
            'property_id' => $this->property->id,
            'source' => 'facebook',
            'views' => 3,
        ]);
    }

    public function test_separa_origenes_distintos(): void
    {
        $this->source->record($this->property, $this->request(['de' => 'facebook']));
        $this->source->record($this->property, $this->request([], ['referer' => 'https://www.google.com/']));

        $this->assertDatabaseCount('property_view_sources', 2);
    }

    public function test_no_cuenta_a_los_bots(): void
    {
        $this->source->record($this->property, $this->request(
            ['de' => 'facebook'],
            [],
            'facebookexternalhit/1.1'
        ));

        $this->assertDatabaseCount('property_view_sources', 0);
    }
}
