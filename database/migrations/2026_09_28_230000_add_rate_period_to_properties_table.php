<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Periodo de cobro de las propiedades reservables. Hasta ahora todo se
     * cobraba por noche; hay departamentos que se rentan por mes.
     *
     * 'noche' como default deja intacto el comportamiento existente.
     */
    public function up(): void
    {
        if (Schema::hasColumn('properties', 'rate_period')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->string('rate_period', 10)->default('noche')->after('price_per_night');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('properties', 'rate_period')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('rate_period');
        });
    }
};
