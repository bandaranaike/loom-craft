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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('status')->default('issued');
            $table->string('currency', 3);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('commission_total', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index('status');
        });

        DB::table('orders')
            ->orderBy('id')
            ->get(['id', 'currency', 'subtotal', 'commission_total', 'total', 'placed_at', 'created_at'])
            ->each(function (object $order): void {
                $timestamp = $order->placed_at ?? $order->created_at ?? now();
                $issuedAt = Carbon::parse($timestamp);

                DB::table('invoices')->insert([
                    'order_id' => $order->id,
                    'invoice_number' => sprintf('INV-%s-%06d', $issuedAt->format('Ym'), $order->id),
                    'status' => 'issued',
                    'currency' => $order->currency,
                    'subtotal' => $order->subtotal,
                    'commission_total' => $order->commission_total,
                    'total' => $order->total,
                    'issued_at' => $issuedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
