<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('public_id');
        });

        DB::table('orders')
            ->orderBy('id')
            ->get(['id', 'placed_at', 'created_at'])
            ->each(function (object $order): void {
                $timestamp = $order->placed_at ?? $order->created_at ?? now();
                $date = Carbon::parse($timestamp);

                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'order_number' => sprintf('ORD-%s-%06d', $date->format('Ym'), $order->id),
                    ]);
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_order_number_unique');
            $table->dropColumn('order_number');
        });
    }
};
