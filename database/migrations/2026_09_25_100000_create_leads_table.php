<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El primer intento en producción creó la tabla pero falló al añadir el
        // índice (status era varchar(255): 1020 bytes, sobre el límite de 1000).
        // Como la migración no quedó registrada, aquí se rehace la tabla, pero
        // solo si está vacía: si ya tiene prospectos, no se toca nada.
        if (Schema::hasTable('leads')) {
            if (DB::table('leads')->count() > 0) {
                return;
            }

            Schema::drop('leads');
        }

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            // Propiedad por la que preguntan. Null = contacto general del sitio.
            $table->unsignedBigInteger('property_id')->nullable();
            // Asesor dueño de la propiedad. Null = lo atiende el admin.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('message');
            // 'contacto' = formulario general, 'propiedad' = ficha de propiedad
            $table->string('source', 20)->default('propiedad');
            // Corto a propósito: entra en el índice compuesto de abajo.
            $table->string('status', 20)->default('nuevo');
            $table->timestamp('responded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
