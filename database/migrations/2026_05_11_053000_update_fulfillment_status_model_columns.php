<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand enum to include all possible values (old + new)
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('status', [
                'pending',
                'paid',
                'confirmed',
                'shipped',
                'delivered',
                'fulfilled',
                'closed',
                'cancelled',
            ])->default('pending')->change();
        });

        // 2. Update data
        DB::table('orders')
            ->where('status', 'shipped')
            ->update(['status' => 'confirmed']);

        DB::table('orders')
            ->where('status', 'delivered')
            ->update(['status' => 'fulfilled']);

        // 3. Shrink enum to final desired values
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('status', [
                'pending',
                'paid',
                'confirmed',
                'fulfilled',
                'closed',
                'cancelled',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        // 1. Expand enum to include all possible values (new + old)
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('status', [
                'pending',
                'paid',
                'confirmed',
                'shipped',
                'delivered',
                'fulfilled',
                'closed',
                'cancelled',
            ])->default('pending')->change();
        });

        // 2. Revert data
        DB::table('orders')
            ->where('status', 'fulfilled')
            ->update(['status' => 'delivered']);

        DB::table('orders')
            ->where('status', 'closed')
            ->update(['status' => 'delivered']);

        // 3. Revert enum to original values
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
};
