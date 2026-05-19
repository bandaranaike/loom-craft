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
            $table->foreignId('complaint_id')
                ->nullable()
                ->after('order_return_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['complaint_id', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fulfillment_status_histories', function (Blueprint $table) {
            $table->dropIndex(['complaint_id', 'domain']);
            $table->dropConstrainedForeignId('complaint_id');
        });
    }
};
