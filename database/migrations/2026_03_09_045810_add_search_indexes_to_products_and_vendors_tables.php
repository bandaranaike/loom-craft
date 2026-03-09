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
        Schema::table('products', function (Blueprint $table): void {
            $table->index(['status', 'selling_price'], 'products_status_selling_price_index');
            $table->index(['status', 'created_at'], 'products_status_created_at_index');
        });

        Schema::table('vendors', function (Blueprint $table): void {
            $table->index(['status', 'display_name'], 'vendors_status_display_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_status_selling_price_index');
            $table->dropIndex('products_status_created_at_index');
        });

        Schema::table('vendors', function (Blueprint $table): void {
            $table->dropIndex('vendors_status_display_name_index');
        });
    }
};
