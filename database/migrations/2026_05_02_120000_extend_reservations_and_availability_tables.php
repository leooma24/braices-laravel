<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('reservations', 'guests')) {
                    $table->unsignedInteger('guests')->default(1)->after('check_out_date');
                }
                if (!Schema::hasColumn('reservations', 'subtotal')) {
                    $table->decimal('subtotal', 10, 2)->nullable()->after('total_price');
                }
                if (!Schema::hasColumn('reservations', 'cleaning_fee_snapshot')) {
                    $table->decimal('cleaning_fee_snapshot', 10, 2)->nullable()->after('subtotal');
                }
                if (!Schema::hasColumn('reservations', 'nights')) {
                    $table->unsignedInteger('nights')->nullable()->after('cleaning_fee_snapshot');
                }
                if (!Schema::hasColumn('reservations', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('nights');
                }
            });

            // Indexes — try/catch porque hasIndex es flaky entre versiones de Laravel.
            $this->safeIndex('reservations', ['property_id', 'status']);
            $this->safeIndex('reservations', ['user_id', 'status']);
            $this->safeIndex('reservations', ['check_in_date', 'check_out_date']);
        }

        if (Schema::hasTable('property_availability')) {
            $this->safeUnique('property_availability', ['property_id', 'date']);
        }

        if (Schema::hasTable('pricing')) {
            $this->safeUnique('pricing', ['property_id', 'date']);
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'reservation_id')) {
                    $table->foreignId('reservation_id')->nullable()->after('user_id')
                        ->constrained('reservations')->nullOnDelete();
                }
            });
            $this->safeUnique('reviews', ['reservation_id']);
        }
    }

    public function down(): void
    {
        // Down sin try/catch — solo se ejecuta en dev.
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'reservation_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique(['reservation_id']);
                $table->dropConstrainedForeignId('reservation_id');
            });
        }

        if (Schema::hasTable('pricing')) {
            try {
                Schema::table('pricing', fn (Blueprint $t) => $t->dropUnique(['property_id', 'date']));
            } catch (\Throwable) {}
        }

        if (Schema::hasTable('property_availability')) {
            try {
                Schema::table('property_availability', fn (Blueprint $t) => $t->dropUnique(['property_id', 'date']));
            } catch (\Throwable) {}
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                foreach (['property_id_status_index', 'user_id_status_index', 'check_in_date_check_out_date_index'] as $idx) {
                    try { $table->dropIndex($idx); } catch (\Throwable) {}
                }
                foreach (['guests', 'subtotal', 'cleaning_fee_snapshot', 'nights', 'expires_at'] as $col) {
                    if (Schema::hasColumn('reservations', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    private function safeIndex(string $table, array $columns): void
    {
        try {
            Schema::table($table, fn (Blueprint $t) => $t->index($columns));
        } catch (\Throwable) {
            // Index ya existe.
        }
    }

    private function safeUnique(string $table, array $columns): void
    {
        try {
            Schema::table($table, fn (Blueprint $t) => $t->unique($columns));
        } catch (\Throwable) {
            // Unique ya existe.
        }
    }
};
