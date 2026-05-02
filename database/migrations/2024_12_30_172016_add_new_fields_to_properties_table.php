<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'is_reservable')) {
                $table->boolean('is_reservable')->default(false);
            }
            if (!Schema::hasColumn('properties', 'max_guests')) {
                $table->integer('max_guests')->nullable();
            }
            if (!Schema::hasColumn('properties', 'price_per_night')) {
                $table->decimal('price_per_night', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('properties', 'cleaning_fee')) {
                $table->decimal('cleaning_fee', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('properties', 'check_in_time')) {
                $table->time('check_in_time')->nullable();
            }
            if (!Schema::hasColumn('properties', 'check_out_time')) {
                $table->time('check_out_time')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['max_guests', 'price_per_night', 'cleaning_fee', 'check_in_time', 'check_out_time']);
        });
    }
};
