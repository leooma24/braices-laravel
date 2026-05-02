<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // package_id deja de ser obligatorio: ahora un payment puede ser por paquete o por reservación.
            $table->unsignedBigInteger('package_id')->nullable()->change();

            $table->foreignId('reservation_id')->nullable()->after('package_id')
                ->constrained('reservations')->nullOnDelete();

            $table->decimal('amount', 10, 2)->nullable()->after('status');
            $table->string('provider')->default('mercadopago')->after('amount');

            $table->index(['reservation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['reservation_id', 'status']);
            $table->dropColumn(['amount', 'provider']);
            $table->dropConstrainedForeignId('reservation_id');
            // No revertimos package_id a NOT NULL para no fallar si hay registros con null.
        });
    }
};
