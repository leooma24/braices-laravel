<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyCoverImageTest extends TestCase
{
    use RefreshDatabase;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        \DB::table('property_status')->insert(['id' => 1, 'name' => 'Activa']);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Venta']);

        $user = User::create([
            'name' => 'owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('secret'),
            'slug' => 'owner',
        ]);

        $this->property = new Property([
            'title' => 'Terreno en Camahuiroa frente al mar',
            'description' => 'desc',
            'address' => 'addr',
            'city' => 'Ahome',
            'state' => 25,
            'country' => 1,
            'zip' => '81200',
            'price' => 3_500_000,
            'square_feet' => 720,
            'property_status_id' => 1,
            'transaction_type_id' => 1,
        ]);
        $this->property->user_id = $user->id;
        $this->property->slug = 'terreno-camahuiroa';
        $this->property->save();
    }

    public function test_cover_url_usa_la_foto_cuando_el_archivo_existe(): void
    {
        $name = 'test-cover-'.uniqid().'.jpg';
        $path = public_path('images/'.$name);
        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, 'jpeg');

        try {
            $this->property->forceFill(['photo_main' => $name])->save();

            $this->assertStringContainsString($name, $this->property->fresh()->coverUrl());
        } finally {
            @unlink($path);
        }
    }

    public function test_cover_url_cae_en_la_portada_si_el_archivo_se_perdio(): void
    {
        $this->property->forceFill(['photo_main' => 'no-existe-en-disco.jpg'])->save();

        $this->assertStringContainsString(
            '/propiedad/portada/'.$this->property->id,
            $this->property->fresh()->coverUrl()
        );
    }

    public function test_cover_url_cae_en_la_portada_generada_sin_foto(): void
    {
        $this->assertStringContainsString(
            '/propiedad/portada/'.$this->property->id,
            $this->property->coverUrl()
        );
    }

    public function test_la_portada_responde_un_jpeg(): void
    {
        if (! function_exists('imagettftext')) {
            $this->markTestSkipped('GD con FreeType no está disponible.');
        }

        // La portada hace eager load de la colonia y las tablas geo solo viven
        // en el MySQL real (ver CLAUDE.md), no en el SQLite de los tests.
        if (! \Schema::hasTable('colonias')) {
            $this->markTestSkipped('Las tablas geo no existen en SQLite.');
        }

        $response = $this->get("/propiedad/portada/{$this->property->id}.jpg");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertNotEmpty($response->streamedContent() ?: $response->getContent());
    }

    public function test_la_portada_de_una_propiedad_inexistente_es_404(): void
    {
        $this->get('/propiedad/portada/999999.jpg')->assertNotFound();
    }
}
