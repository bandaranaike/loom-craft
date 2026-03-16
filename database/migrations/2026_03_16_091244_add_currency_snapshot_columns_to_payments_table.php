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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('original_amount', 10, 2)->nullable()->after('currency');
            $table->string('original_currency', 3)->nullable()->after('original_amount');
            $table->decimal('exchange_rate', 18, 8)->nullable()->after('original_currency');
            $table->string('exchange_rate_source')->nullable()->after('exchange_rate');
            $table->timestamp('exchange_rate_fetched_at')->nullable()->after('exchange_rate_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'original_amount',
                'original_currency',
                'exchange_rate',
                'exchange_rate_source',
                'exchange_rate_fetched_at',
            ]);
        });
    }
};
