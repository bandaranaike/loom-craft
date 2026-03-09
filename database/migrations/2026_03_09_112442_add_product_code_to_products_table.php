<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 100)->nullable()->after('name');
        });

        DB::table('products')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'product_code' => sprintf('PRD-%06d', $product->id),
                        ]);
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 100)->nullable(false)->change();
            $table->unique('product_code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_code']);
            $table->dropColumn('product_code');
        });
    }
};
