<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyContactClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactClickTrackingTest extends TestCase
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

    public function test_registra_un_clic_de_whatsapp(): void
    {
        $this->post("/propiedad/{$this->property->id}/contacto/whatsapp")
            ->assertNoContent();

        $this->assertDatabaseHas('property_contact_clicks', [
            'property_id' => $this->property->id,
            'channel' => 'whatsapp',
        ]);
    }

    public function test_rechaza_un_canal_inventado(): void
    {
        $this->post("/propiedad/{$this->property->id}/contacto/telegram")
            ->assertNotFound();

        $this->assertSame(0, PropertyContactClick::count());
    }

    public function test_rechaza_una_propiedad_inexistente(): void
    {
        $this->post('/propiedad/999999/contacto/whatsapp')->assertNotFound();

        $this->assertSame(0, PropertyContactClick::count());
    }
}
