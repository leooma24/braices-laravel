<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function sitemap_lists_active_properties_only()
    {
        \DB::table('property_status')->insert([
            ['id' => 1, 'name' => 'Activa'],
            ['id' => 2, 'name' => 'Vendida'],
        ]);
        \DB::table('transaction_types')->insert(['id' => 1, 'name' => 'Venta']);

        $user = User::create([
            'name' => 'owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('secret'),
            'slug' => 'owner',
        ]);

        $this->makeProperty($user, 'casa-activa', 1);
        $this->makeProperty($user, 'casa-vendida', 2);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('xml', $response->headers->get('Content-Type'));
        $response->assertSee('/propiedad/casa-activa', false);
        $response->assertDontSee('/propiedad/casa-vendida', false);
    }

    /** @test */
    public function static_robots_txt_points_to_sitemap()
    {
        // public/robots.txt tapa la ruta /robots.txt en el servidor, así que debe incluir el sitemap.
        $this->assertStringContainsString(
            'Sitemap: https://bienescorp.com/sitemap.xml',
            file_get_contents(public_path('robots.txt'))
        );
    }

    private function makeProperty(User $user, string $slug, int $statusId): Property
    {
        $property = new Property([
            'title' => $slug,
            'description' => 'desc',
            'address' => 'addr',
            'city' => 'Los Mochis',
            'state' => 25,
            'country' => 1,
            'zip' => '81245',
            'price' => 1_000_000,
            'square_feet' => 100,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'square_meters_contruction' => 80,
            'levels' => 1,
            'property_status_id' => $statusId,
            'transaction_type_id' => 1,
        ]);
        $property->user_id = $user->id;
        $property->slug = $slug;
        $property->save();

        return $property;
    }
}
