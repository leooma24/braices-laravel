<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Renta',
            'Transpaso',
            'Venta',
            'Renta / Venta',
            'Adjudicación',
        ];
        foreach($types as $type) {
            \App\Models\TransactionTypeModel::create([
                'name' => $type,
            ]);
        }
    }
}
