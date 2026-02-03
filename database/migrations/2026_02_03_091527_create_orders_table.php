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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('status');
            $table->string('currency');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('commission_total', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('shipping_responsibility');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
