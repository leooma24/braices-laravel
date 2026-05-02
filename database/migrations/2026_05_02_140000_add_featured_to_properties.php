<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_reservable');
            $table->timestamp('featured_until')->nullable()->after('is_featured');
            $table->index(['is_featured', 'featured_until']);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'featured_until']);
            $table->dropColumn(['is_featured', 'featured_until']);
        });
    }
};
