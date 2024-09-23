<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Disponible',
            'Rentada',
            'Vendida',
            'Cancelada',
        ];
        foreach($types as $type) {
            \App\Models\PropertyStatusModel::create([
                'name' => $type,
            ]);
        }
    }
}
