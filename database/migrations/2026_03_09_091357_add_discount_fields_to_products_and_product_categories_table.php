<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('selling_price');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });
    }
};
