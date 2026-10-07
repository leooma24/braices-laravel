<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_contact_clicks')) {
            return;
        }

        // Los contactos reales no llegan por el formulario: la gente le pica a
        // WhatsApp y la conversacion se va fuera del sitio, sin dejar rastro.
        // Aqui se registra ese clic para saber que propiedad genera platica.
        Schema::create('property_contact_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            // 'whatsapp' | 'correo' | 'telefono'
            $table->string('channel', 20);
            $table->timestamp('created_at')->nullable();

            $table->index(['property_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_contact_clicks');
    }
};
