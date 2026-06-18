<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('dimension_length', 10, 2)->nullable()->after('selling_price');
            $table->decimal('dimension_width', 10, 2)->nullable()->after('dimension_length');
            $table->decimal('dimension_height', 10, 2)->nullable()->after('dimension_width');
        });

        DB::table('product_variations')->update([
            'dimension_length' => DB::raw('(select products.dimension_length from products where products.id = product_variations.product_id)'),
            'dimension_width' => DB::raw('(select products.dimension_width from products where products.id = product_variations.product_id)'),
            'dimension_height' => DB::raw('(select products.dimension_height from products where products.id = product_variations.product_id)'),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'dimension_length',
                'dimension_width',
                'dimension_height',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('dimension_length', 10, 2)->nullable()->after('production_time_days');
            $table->decimal('dimension_width', 10, 2)->nullable()->after('dimension_length');
            $table->decimal('dimension_height', 10, 2)->nullable()->after('dimension_width');
        });

        DB::table('products')->update([
            'dimension_length' => DB::raw('(select product_variations.dimension_length from product_variations where product_variations.product_id = products.id order by product_variations.sort_order, product_variations.id limit 1)'),
            'dimension_width' => DB::raw('(select product_variations.dimension_width from product_variations where product_variations.product_id = products.id order by product_variations.sort_order, product_variations.id limit 1)'),
            'dimension_height' => DB::raw('(select product_variations.dimension_height from product_variations where product_variations.product_id = products.id order by product_variations.sort_order, product_variations.id limit 1)'),
        ]);

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn([
                'dimension_length',
                'dimension_width',
                'dimension_height',
            ]);
        });
    }
};
