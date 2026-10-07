<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_view_sources')) {
            return;
        }

        // `properties.views` dice cuanta gente entro, no de donde venia, asi que
        // no se podia saber si una publicacion en Facebook trajo visitas o si
        // era solo el efecto de ser la propiedad mas reciente del listado.
        //
        // Se guarda agregado (una fila por propiedad, origen y dia) en vez de
        // una fila por visita: el reporte es una consulta simple y la tabla no
        // crece sin control.
        Schema::create('property_view_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            // 'facebook', 'google', 'interno', 'directo', 'otro'...
            $table->string('source', 30);
            $table->date('day');
            $table->unsignedInteger('views')->default(0);

            $table->unique(['property_id', 'source', 'day']);
            $table->index('day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_view_sources');
    }
};
