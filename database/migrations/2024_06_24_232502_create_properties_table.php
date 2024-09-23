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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address');
            $table->int('suburb');
            $table->int('city');
            $table->int('township');
            $table->int('state');
            $table->int('country');
            $table->string('zip');
            $table->string('price')->default(0);
            $table->tinyInteger('bedrooms')->nullable();
            $table->tinyInteger('bathrooms')->nullable();
            $table->float('square_feet')->nullable();
            $table->float('square_meters_contruction')->nullable();
            $table->float('front')->nullable();
            $table->float('depth')->nullable();
            $table->tinyInteger('levels')->nullable();
            $table->string('lot_size')->nullable();
            $table->integer('year_built')->nullable();
            $table->string('photo_main')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('youtube')->nullable();
            $table->string('slug')->unique();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_status_id')->constrained('property_status')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
