<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        // package_id pasa a nullable. Solo aplica si no es ya nullable.
        // Requiere doctrine/dbal — instalado en este proyecto.
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('package_id')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Si falla (p. ej. ya es nullable, o engine no lo soporta), seguimos.
        }

        // Columnas. Agregamos reservation_id SIN la FK formal — en prod la tabla
        // payments es antigua (latin1) mientras que reservations es utf8mb4,
        // por lo que MySQL rechaza el FK con errno 150. La integridad la
        // garantizamos a nivel de app (los webhooks validan reservation_id).
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'reservation_id')) {
                $table->unsignedBigInteger('reservation_id')->nullable()->after('package_id');
            }
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'provider')) {
                $table->string('provider')->default('mercadopago')->after('amount');
            }
        });

        // FK best-effort: en dev/test (utf8mb4 limpio) sí se crea; en prod
        // legado puede fallar por charset mismatch — no es fatal.
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreign('reservation_id')
                    ->references('id')->on('reservations')
                    ->nullOnDelete();
            });
        } catch (\Throwable) {
            // FK ya existe o charset mismatch — seguimos.
        }

        try {
            Schema::table('payments', fn (Blueprint $t) => $t->index(['reservation_id', 'status']));
        } catch (\Throwable) {
            // Index ya existe.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            try { $table->dropIndex(['reservation_id', 'status']); } catch (\Throwable) {}
            try { $table->dropForeign(['reservation_id']); } catch (\Throwable) {}
            foreach (['amount', 'provider', 'reservation_id'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
