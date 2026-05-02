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

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'reservation_id')) {
                $table->foreignId('reservation_id')->nullable()->after('package_id')
                    ->constrained('reservations')->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'provider')) {
                $table->string('provider')->default('mercadopago')->after('amount');
            }
        });

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
            foreach (['amount', 'provider'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('payments', 'reservation_id')) {
                $table->dropConstrainedForeignId('reservation_id');
            }
        });
    }
};
