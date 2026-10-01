<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_view_snapshots')) {
            return;
        }

        // Foto diaria del contador `properties.views`, que es acumulado y sin
        // fecha. Con dos fotos se puede calcular el movimiento de un periodo
        // (p. ej. cuántas visitas trajo una publicación en Facebook).
        Schema::create('property_view_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->unsignedInteger('views')->default(0);
            // Solo la fecha: una foto por propiedad por día.
            $table->date('captured_on');
            $table->timestamps();

            $table->unique(['property_id', 'captured_on']);
            $table->index('captured_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_view_snapshots');
    }
};
