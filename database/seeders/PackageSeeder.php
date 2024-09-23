<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Package::create([
            'name' => 'Paquete 1',
            'price' => 100,
            'max_listings' => 5,
            'duration' => 30,
        ]);

        Package::create([
            'name' => 'Paquete 2',
            'price' => 200,
            'max_listings' => 10,
            'duration' => 60,
        ]);
    }
}
