<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'shipped')
            ->update(['status' => 'confirmed']);

        DB::table('orders')
            ->where('status', 'delivered')
            ->update(['status' => 'fulfilled']);

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
        DB::table('orders')
            ->where('status', 'fulfilled')
            ->update(['status' => 'delivered']);

        DB::table('orders')
            ->where('status', 'closed')
            ->update(['status' => 'delivered']);

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
