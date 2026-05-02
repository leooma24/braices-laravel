<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_reservable');
            }
            if (!Schema::hasColumn('properties', 'featured_until')) {
                $table->timestamp('featured_until')->nullable()->after('is_featured');
            }
        });

        try {
            Schema::table('properties', fn (Blueprint $t) => $t->index(['is_featured', 'featured_until']));
        } catch (\Throwable) {
            // Index ya existe.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            try { $table->dropIndex(['is_featured', 'featured_until']); } catch (\Throwable) {}
            foreach (['is_featured', 'featured_until'] as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
