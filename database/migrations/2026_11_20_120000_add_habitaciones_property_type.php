<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El catalogo tenia "Casa Habitacion" (una casa) pero no un tipo para el
 * cuarto que se renta por dia, asi que esos anuncios se publicaban como
 * "Departamentos".
 */
return new class extends Migration
{
    private const NOMBRE = 'Habitaciones';

    public function up(): void
    {
        $existe = DB::table('property_types')->where('name', self::NOMBRE)->exists();

        if ($existe) {
            return;
        }

        $fila = ['name' => self::NOMBRE];

        // La tabla se construyo a mano en prod antes de usar migraciones:
        // no se puede dar por hecho que tenga timestamps.
        if (DB::getSchemaBuilder()->hasColumn('property_types', 'created_at')) {
            $fila['created_at'] = now();
            $fila['updated_at'] = now();
        }

        DB::table('property_types')->insert($fila);
    }

    public function down(): void
    {
        // Solo se borra si nadie la esta usando: si ya hay anuncios con este
        // tipo, quitarlo los dejaria sin clasificacion.
        $id = DB::table('property_types')->where('name', self::NOMBRE)->value('id');

        if (! $id) {
            return;
        }

        $enUso = DB::table('property_property_type')->where('property_type_id', $id)->exists();

        if (! $enUso) {
            DB::table('property_types')->where('id', $id)->delete();
        }
    }
};
