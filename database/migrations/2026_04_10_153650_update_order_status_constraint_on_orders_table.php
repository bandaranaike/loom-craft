<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->hasInvalidOrderStatuses()) {
            throw new RuntimeException('The orders table contains unsupported status values. Normalize existing data before running this migration.');
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('status', [
                'pending',
                'paid',
                'confirmed',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('status')->change();
        });
    }

    private function hasInvalidOrderStatuses(): bool
    {
        return DB::table('orders')
            ->whereNotIn('status', [
                'pending',
                'paid',
                'confirmed',
                'shipped',
                'delivered',
                'cancelled',
            ])
            ->exists();
    }
};
