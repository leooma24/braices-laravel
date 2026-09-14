<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Property;
use App\Models\PropertyTypeModel;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PropertyCreateImagesTest extends TestCase
{
    use RefreshDatabase;

    /** Fotos que create() movió a public/images y hay que borrar al terminar. */
    private array $storedPhotos = [];

    protected function tearDown(): void
    {
        foreach ($this->storedPhotos as $photo) {
            @unlink(public_path('images/'.$photo));
        }

        parent::tearDown();
    }

    /** @test */
    public function creating_a_property_saves_gallery_images()
    {
        \DB::table('property_status')->insert(['id' => 1, 'name' => 'Activa']);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Venta']);
        $type = PropertyTypeModel::create(['name' => 'Casa Habitación']);

        $user = User::create([
            'name' => 'owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('secret'),
            'slug' => 'owner',
        ]);
        $user->email_verified_at = now();
        $user->save();

        $package = Package::create([
            'name' => 'Test Package',
            'price' => 100,
            'max_listings' => 5,
            'duration' => 30,
        ]);
        $userPackage = UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'remaining_listings' => 5,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->post('/propiedad/guardar', [
            'property_type_id' => [$type->id],
            'transaction_type_id' => 1,
            'property_status_id' => 1,
            'title' => 'Casa con galería',
            'address' => 'Calle Independencia 1361',
            'price' => '2,490,000',
            'bedrooms' => 1,
            'bathrooms' => 2,
            'square_feet' => 234.36,
            'square_meters_contruction' => 144.78,
            'levels' => 2,
            'description' => 'Casa de dos plantas.',
            'country' => '1',
            'state' => '25',
            'city' => 'Los Mochis',
            'suburb' => '250012449',
            'zip' => '81245',
            'images' => [
                UploadedFile::fake()->image('sala.jpg'),
                UploadedFile::fake()->image('bano.jpg'),
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('myProperties'));

        $property = Property::where('title', 'Casa con galería')->firstOrFail();
        // Valor crudo de la columna: el modelo PropertyImage expone "photo" como URL.
        $this->storedPhotos = \DB::table('property_images')->where('property_id', $property->id)->pluck('photo')->all();

        $this->assertCount(2, $this->storedPhotos);
        foreach ($this->storedPhotos as $photo) {
            $this->assertFileExists(public_path('images/'.$photo));
        }
        $this->assertSame(4, $userPackage->fresh()->remaining_listings);
    }
}
