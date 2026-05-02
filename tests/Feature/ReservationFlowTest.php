<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationAvailabilityService;
use App\Services\ReservationPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $host;
    private User $guest;
    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed FK references requeridos por properties.
        \DB::table('property_status')->insert(['id' => 1, 'name' => 'Activa']);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Renta']);

        $this->host = $this->makeVerifiedUser('host@test.com', 'Host', 'host');
        $this->guest = $this->makeVerifiedUser('guest@test.com', 'Guest', 'guest');

        $this->property = new Property([
            'title' => 'Casa de Playa',
            'description' => 'Hermosa casa frente al mar',
            'address' => 'Playa 100',
            'city' => 'Mazatlán',
            'state' => 25,
            'country' => 1,
            'zip' => '82000',
            'price' => 2_000_000,
            'price_per_night' => 1500,
            'cleaning_fee' => 300,
            'max_guests' => 4,
            'is_reservable' => true,
            'square_feet' => 200,
            'property_status_id' => 1,
            'transaction_type_id' => 1,
        ]);
        $this->property->user_id = $this->host->id;
        $this->property->slug = 'casa-de-playa';
        $this->property->save();
    }

    private function makeVerifiedUser(string $email, string $name, string $slug): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('secret'),
            'slug' => $slug,
        ]);
        $user->email_verified_at = now();
        $user->save();
        return $user;
    }

    /** @test */
    public function quote_calculates_total_correctly()
    {
        $pricing = app(ReservationPricingService::class);
        $quote = $pricing->quote($this->property, '2026-08-01', '2026-08-04');

        $this->assertEquals(3, $quote['nights']);
        $this->assertEquals('4500.00', $quote['subtotal']);
        $this->assertEquals('300.00', $quote['cleaning_fee']);
        $this->assertEquals('4800.00', $quote['total']);
    }

    /** @test */
    public function availability_service_blocks_overlapping_reservations()
    {
        Reservation::create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-13',
            'guests' => 2,
            'status' => ReservationStatus::Confirmada,
            'nights' => 3,
            'subtotal' => 4500,
            'cleaning_fee_snapshot' => 300,
            'total_price' => 4800,
        ]);

        $avail = app(ReservationAvailabilityService::class);

        $this->assertFalse($avail->isAvailable($this->property, '2026-08-11', '2026-08-14'), 'Solapamiento debe bloquear');
        $this->assertFalse($avail->isAvailable($this->property, '2026-08-09', '2026-08-11'), 'Solapamiento por inicio bloquea');
        $this->assertTrue($avail->isAvailable($this->property, '2026-08-13', '2026-08-15'), 'Adyacente (checkout=checkin) disponible');
        $this->assertTrue($avail->isAvailable($this->property, '2026-08-07', '2026-08-10'), 'Adyacente antes disponible');
    }

    /** @test */
    public function blocked_dates_returns_only_occupied_nights_excluding_checkout()
    {
        Reservation::create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-13',
            'guests' => 2,
            'status' => ReservationStatus::Confirmada,
            'nights' => 3,
            'subtotal' => 4500,
            'cleaning_fee_snapshot' => 300,
            'total_price' => 4800,
        ]);

        $avail = app(ReservationAvailabilityService::class);
        $blocked = $avail->blockedDates($this->property, '2026-08-01', '2026-08-31');

        $this->assertContains('2026-08-10', $blocked);
        $this->assertContains('2026-08-11', $blocked);
        $this->assertContains('2026-08-12', $blocked);
        $this->assertNotContains('2026-08-13', $blocked, 'Día de checkout no debe bloquearse');
    }

    /** @test */
    public function guest_creates_pending_reservation()
    {
        $response = $this->actingAs($this->guest)
            ->post('/reservaciones', [
                'property_id' => $this->property->id,
                'check_in' => '2026-09-01',
                'check_out' => '2026-09-04',
                'guests' => 2,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'guests' => 2,
            'nights' => 3,
            'status' => 'pendiente',
        ]);
    }

    /** @test */
    public function reservation_rejects_overlapping_dates()
    {
        Reservation::create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'check_in_date' => '2026-09-10',
            'check_out_date' => '2026-09-13',
            'guests' => 2,
            'status' => ReservationStatus::Confirmada,
            'nights' => 3,
            'subtotal' => 4500,
            'cleaning_fee_snapshot' => 300,
            'total_price' => 4800,
        ]);

        $other = $this->makeVerifiedUser('other@test.com', 'Other', 'other');

        $response = $this->actingAs($other)
            ->post('/reservaciones', [
                'property_id' => $this->property->id,
                'check_in' => '2026-09-11',
                'check_out' => '2026-09-14',
                'guests' => 1,
            ]);

        $response->assertSessionHasErrors('check_in');
    }

    /** @test */
    public function unauthenticated_user_cannot_create_reservation()
    {
        $response = $this->post('/reservaciones', [
            'property_id' => $this->property->id,
            'check_in' => '2026-09-01',
            'check_out' => '2026-09-04',
            'guests' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function only_owner_can_cancel_reservation()
    {
        $reservation = Reservation::create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(13)->toDateString(),
            'guests' => 1,
            'status' => ReservationStatus::Confirmada,
            'nights' => 3,
            'subtotal' => 4500,
            'cleaning_fee_snapshot' => 300,
            'total_price' => 4800,
        ]);

        $intruder = $this->makeVerifiedUser('intruder@test.com', 'Intruder', 'intruder');

        $response = $this->actingAs($intruder)
            ->post('/reservaciones/' . $reservation->id . '/cancelar');

        $response->assertForbidden();

        // El dueño sí puede
        $response = $this->actingAs($this->guest)
            ->post('/reservaciones/' . $reservation->id . '/cancelar');

        $response->assertRedirect();
        $this->assertEquals('cancelada', $reservation->fresh()->status->value);
    }

    /** @test */
    public function release_expired_command_cancels_pending_reservations()
    {
        $reservation = Reservation::create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-04',
            'guests' => 1,
            'status' => ReservationStatus::Pendiente,
            'nights' => 3,
            'subtotal' => 4500,
            'cleaning_fee_snapshot' => 300,
            'total_price' => 4800,
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('reservations:release-expired')->assertSuccessful();

        $this->assertEquals('cancelada', $reservation->fresh()->status->value);
    }

    /** @test */
    public function complete_past_command_marks_finished_stays_completed()
    {
        $reservation = Reservation::create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'check_in_date' => now()->subDays(5)->toDateString(),
            'check_out_date' => now()->subDay()->toDateString(),
            'guests' => 1,
            'status' => ReservationStatus::Confirmada,
            'nights' => 4,
            'subtotal' => 6000,
            'cleaning_fee_snapshot' => 300,
            'total_price' => 6300,
        ]);

        $this->artisan('reservations:complete-past')->assertSuccessful();

        $this->assertEquals('completada', $reservation->fresh()->status->value);
    }
}
