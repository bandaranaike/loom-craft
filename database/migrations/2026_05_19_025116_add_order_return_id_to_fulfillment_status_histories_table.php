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
        Schema::table('fulfillment_status_histories', function (Blueprint $table) {
            $table->foreignId('order_return_id')
                ->nullable()
                ->after('shipment_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['order_return_id', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulfillment_status_histories', function (Blueprint $table) {
            $table->dropIndex(['order_return_id', 'domain']);
            $table->dropConstrainedForeignId('order_return_id');
        });
    }
};
