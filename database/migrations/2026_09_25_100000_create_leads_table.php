<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->string('source')->default('propiedad');
            $table->string('status')->default('nuevo');
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
