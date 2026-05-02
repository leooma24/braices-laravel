<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedInteger('guests')->default(1)->after('check_out_date');
            $table->decimal('subtotal', 10, 2)->nullable()->after('total_price');
            $table->decimal('cleaning_fee_snapshot', 10, 2)->nullable()->after('subtotal');
            $table->unsignedInteger('nights')->nullable()->after('cleaning_fee_snapshot');
            $table->timestamp('expires_at')->nullable()->after('nights');
            $table->index(['property_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['check_in_date', 'check_out_date']);
        });

        Schema::table('property_availability', function (Blueprint $table) {
            $table->unique(['property_id', 'date']);
        });

        Schema::table('pricing', function (Blueprint $table) {
            $table->unique(['property_id', 'date']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->after('user_id')
                ->constrained('reservations')->nullOnDelete();
            $table->unique('reservation_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['reservation_id']);
            $table->dropConstrainedForeignId('reservation_id');
        });

        Schema::table('pricing', function (Blueprint $table) {
            $table->dropUnique(['property_id', 'date']);
        });

        Schema::table('property_availability', function (Blueprint $table) {
            $table->dropUnique(['property_id', 'date']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['check_in_date', 'check_out_date']);
            $table->dropColumn(['guests', 'subtotal', 'cleaning_fee_snapshot', 'nights', 'expires_at']);
        });
    }
};
