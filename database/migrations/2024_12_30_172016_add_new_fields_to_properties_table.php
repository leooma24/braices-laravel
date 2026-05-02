<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            //
            $table->boolean('is_reservable')->default(false);
            $table->integer('max_guests')->nullable();
            $table->decimal('price_per_night', 10, 2)->nullable();
            $table->decimal('cleaning_fee', 10, 2)->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            //
            $table->dropColumn(['max_guests', 'price_per_night', 'cleaning_fee', 'check_in_time', 'check_out_time']);
        });
    }
};
