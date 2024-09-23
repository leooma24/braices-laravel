<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Bodegas',
            'Casa Comercial',
            'Departamentos',
            'Casa Habitación',
            'Terrenos Residenciales',
            'Terrenos Comerciales',
            'Terrenos Industriales',
            'Oficinas',
            'Locales Comerciales',
            'Edificios',
            'Terrenos Agrícolas',
            'Terrenos Campestres',
            'Terrenos Turísticos',
            'Terrenos Ejidales',
            'Terrenos Industriales',
            'Casas de Playa',
            'Proyecto Residencial',
            'Proyecto Comercial',
            'Proyecto Industrial',
        ];
        foreach($types as $type) {
            \App\Models\PropertyTypeModel::create([
                'name' => $type,
            ]);
        }
    }
}
