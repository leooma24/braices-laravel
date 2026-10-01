<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyViewSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyViewSnapshotTest extends TestCase
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
            'title' => 'Casa en El Pueblito',
            'description' => 'desc',
            'address' => 'addr',
            'city' => 'Los Mochis',
            'state' => 25,
            'country' => 1,
            'zip' => '81200',
            'price' => 2_490_000,
            'square_feet' => 120,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_meters_contruction' => 100,
            'levels' => 2,
            'property_status_id' => 1,
            'transaction_type_id' => 1,
        ]);
        $this->property->user_id = $user->id;
        $this->property->slug = 'casa-en-el-pueblito';
        $this->property->views = 100;
        $this->property->save();
    }

    public function test_snapshot_guarda_el_contador_del_dia(): void
    {
        $this->artisan('views:snapshot')->assertSuccessful();

        $this->assertDatabaseHas('property_view_snapshots', [
            'property_id' => $this->property->id,
            'views' => 100,
            'captured_on' => now()->toDateString(),
        ]);
    }

    public function test_correr_dos_veces_el_mismo_dia_no_duplica_la_foto(): void
    {
        $this->artisan('views:snapshot')->assertSuccessful();

        $this->property->forceFill(['views' => 150])->save();
        $this->artisan('views:snapshot')->assertSuccessful();

        $snapshots = PropertyViewSnapshot::where('property_id', $this->property->id)->get();
        $this->assertCount(1, $snapshots);
        $this->assertSame(150, $snapshots->first()->views);
    }

    public function test_report_calcula_las_vistas_nuevas_del_periodo(): void
    {
        // Foto de hace 7 días con 100 vistas; hoy la propiedad va en 261.
        $this->artisan('views:snapshot', ['--date' => now()->subDays(7)->toDateString()])
            ->assertSuccessful();

        $this->property->forceFill(['views' => 261])->save();

        $this->artisan('views:report', ['--days' => 7])
            ->expectsOutputToContain('+161')
            ->assertSuccessful();
    }

    public function test_report_avisa_cuando_todavia_no_hay_fotos(): void
    {
        $this->artisan('views:report')
            ->expectsOutputToContain('Todavía no hay fotos guardadas')
            ->assertSuccessful();
    }
}
