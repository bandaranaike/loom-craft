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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('order_item_id')->nullable()->constrained();
            $table->foreignId('opened_by_user_id')->nullable()->constrained('users');
            $table->string('status');
            $table->text('reason');
            $table->text('resolution')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
