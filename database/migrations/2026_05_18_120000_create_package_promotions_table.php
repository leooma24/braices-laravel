<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('package_promotions')) {
            return;
        }

        Schema::create('package_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->unsignedTinyInteger('discount_percent');
            $table->unsignedInteger('from_count')->default(1);
            $table->unsignedInteger('to_count');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['package_id', 'is_active']);
        });

        // Seed inicial: 50% off en las primeras 20 compras de cada paquete.
        foreach (\App\Models\Package::all() as $pkg) {
            \DB::table('package_promotions')->insert([
                'package_id' => $pkg->id,
                'label' => 'Oferta de lanzamiento',
                'discount_percent' => 50,
                'from_count' => 1,
                'to_count' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('package_promotions');
    }
};
