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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variation_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variations')
                ->nullOnDelete();
            $table->string('product_variation_label')->nullable()->after('product_variation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variation_id');
            $table->dropColumn('product_variation_label');
        });
    }
};
