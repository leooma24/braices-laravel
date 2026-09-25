<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadInboxTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $intruder;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        \DB::table('property_status')->insert(['id' => 1, 'name' => 'Activa']);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Venta']);

        $this->owner = $this->makeUser('owner@test.com', 'owner');
        $this->intruder = $this->makeUser('intruder@test.com', 'intruder');

        $this->property = new Property([
            'title' => 'Casa del Owner',
            'description' => 'desc',
            'address' => 'addr',
            'city' => 'Los Mochis',
            'state' => 25,
            'country' => 1,
            'zip' => '81200',
            'price' => 2_490_000,
            'square_feet' => 234,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'square_meters_contruction' => 145,
            'levels' => 2,
            'property_status_id' => 1,
            'transaction_type_id' => 1,
        ]);
        $this->property->user_id = $this->owner->id;
        $this->property->slug = 'casa-del-owner';
        $this->property->save();
    }

    private function makeUser(string $email, string $slug): User
    {
        $user = User::create([
            'name' => $slug,
            'email' => $email,
            'password' => bcrypt('secret'),
            'slug' => $slug,
        ]);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function makeLead(array $overrides = []): Lead
    {
        return Lead::create(array_merge([
            'property_id' => $this->property->id,
            'user_id' => $this->owner->id,
            'name' => 'Juan Comprador',
            'email' => 'juan@test.com',
            'phone' => '6682493398',
            'message' => 'Me interesa la casa, ¿sigue disponible?',
            'source' => 'propiedad',
        ], $overrides));
    }

    /** @test */
    public function contact_form_saves_the_lead_even_if_the_mail_fails()
    {
        // El captcha se simula: en pruebas no hay llamada real a Google.
        \Anhskohbo\NoCaptcha\Facades\NoCaptcha::shouldReceive('verifyResponse')->andReturn(true);

        // Si el correo truena, el prospecto tiene que quedar guardado de todos modos.
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP caído'));

        $response = $this->post('/contactame', [
            'property_id' => $this->property->id,
            'name' => 'Juan Comprador',
            'phone_number' => '6682493398',
            'email' => 'juan@test.com',
            'message' => 'Me interesa la casa',
            'g-recaptcha-response' => 'test',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'property_id' => $this->property->id,
            'user_id' => $this->owner->id,
            'email' => 'juan@test.com',
            'status' => 'nuevo',
        ]);
    }

    /** @test */
    public function owner_sees_their_leads_in_the_inbox()
    {
        $this->makeLead();

        $response = $this->actingAs($this->owner)->get('/cuenta/prospectos');

        $response->assertOk();
        $response->assertSee('Juan Comprador');
    }

    /** @test */
    public function intruder_does_not_see_leads_of_other_users()
    {
        $this->makeLead();

        $response = $this->actingAs($this->intruder)->get('/cuenta/prospectos');

        $response->assertOk();
        $response->assertDontSee('Juan Comprador');
    }

    /** @test */
    public function intruder_cannot_change_the_status_of_someone_elses_lead()
    {
        $lead = $this->makeLead();

        $response = $this->actingAs($this->intruder)
            ->post('/cuenta/prospectos/'.$lead->id.'/estado', ['status' => 'cerrado']);

        $response->assertNotFound();
        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'nuevo']);
    }

    /** @test */
    public function marking_a_lead_as_answered_records_the_time()
    {
        $lead = $this->makeLead();

        $response = $this->actingAs($this->owner)
            ->post('/cuenta/prospectos/'.$lead->id.'/estado', [
                'status' => 'contestado',
                'notes' => 'Le mandé fotos por WhatsApp',
            ]);

        $response->assertRedirect();

        $lead->refresh();
        $this->assertSame('contestado', $lead->status);
        $this->assertSame('Le mandé fotos por WhatsApp', $lead->notes);
        $this->assertNotNull($lead->responded_at);
    }

    /** @test */
    public function a_new_lead_becomes_urgent_after_two_hours()
    {
        $reciente = $this->makeLead();
        $viejo = $this->makeLead(['name' => 'Ana Tardada']);
        $viejo->created_at = now()->subHours(3);
        $viejo->save();

        $this->assertFalse($reciente->isUrgent());
        $this->assertTrue($viejo->fresh()->isUrgent());
    }

    /** @test */
    public function an_answered_lead_is_never_urgent()
    {
        $lead = $this->makeLead(['status' => 'contestado']);
        $lead->created_at = now()->subDays(2);
        $lead->save();

        $this->assertFalse($lead->fresh()->isUrgent());
    }

    /** @test */
    public function whatsapp_link_uses_the_mexican_country_code()
    {
        $lead = $this->makeLead();

        $this->assertStringStartsWith('https://wa.me/526682493398?text=', $lead->whatsappUrl());
    }
}
