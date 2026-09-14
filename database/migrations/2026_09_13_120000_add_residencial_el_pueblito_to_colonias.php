<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MUNICIPIO_AHOME = 25001;

    private const NOMBRE = 'Residencial El Pueblito';

    private const CODIGO_POSTAL = 81245;

    public function up(): void
    {
        // Las tablas geo solo existen en MySQL real (no en el SQLite de tests).
        if (! Schema::hasTable('colonias')) {
            return;
        }

        $exists = DB::table('colonias')
            ->where('nombre', self::NOMBRE)
            ->where('codigo_postal', self::CODIGO_POSTAL)
            ->exists();

        if ($exists) {
            return;
        }

        // Los ids siguen el formato estado+municipio+consecutivo (ej. 250012100),
        // así que se usa el siguiente consecutivo dentro de Ahome.
        $nextId = (int) DB::table('colonias')
            ->where('municipio', self::MUNICIPIO_AHOME)
            ->max('id') + 1;

        DB::table('colonias')->insert([
            'id' => $nextId,
            'nombre' => self::NOMBRE,
            'ciudad' => 'Los Mochis',
            'municipio' => self::MUNICIPIO_AHOME,
            'asentamiento' => 'Fraccionamiento',
            'codigo_postal' => self::CODIGO_POSTAL,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('colonias')) {
            return;
        }

        DB::table('colonias')
            ->where('nombre', self::NOMBRE)
            ->where('codigo_postal', self::CODIGO_POSTAL)
            ->delete();
    }
};
