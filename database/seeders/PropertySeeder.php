<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $no_of_rows = 50;
        $user_data = [];

        for( $i=1; $i <= $no_of_rows; $i++ ){
            $title = ucfirst(fake()->words(3, true));
            $slug = Str::slug($title);
            $user_data[] = [
                'user_id' => 1,
                'property_type_id' => fake()->numberBetween(1, 18),
                'property_status_id' => fake()->numberBetween(1, 4),
                'transaction_type_id' => fake()->numberBetween(1, 5),
                'title' => ucfirst(fake()->words(3, true)),
                'description' => fake()->text(),
                'address' => fake()->address(),
                'city' => 'Cancún',
                'state' => 'Quintana Roo',
                'country' => 'México',
                'zip' => fake()->numberBetween(81200, 81900),
                'price' => fake()->numberBetween(100000, 1000000),
                'bedrooms' => fake()->numberBetween(1, 5),
                'bathrooms' => fake()->numberBetween(1, 3),
                'square_feet' => fake()->numberBetween(1000, 5000),
                'lot_size' => fake()->numberBetween(2000, 10000),
                'year_built' => fake()->numberBetween(2000, 2021),
                'photo_main' => 'sin-imagen.jpg',
                'slug' => $slug
            ];

        }

        \App\Models\Property::insert($user_data);

    }
}
