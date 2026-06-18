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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('vendor_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'label']);
            $table->index(['product_id', 'sort_order']);
        });

        DB::table('products')
            ->orderBy('id')
            ->select(['id', 'vendor_price', 'selling_price', 'created_at', 'updated_at'])
            ->chunkById(100, function ($products): void {
                $now = now();

                DB::table('product_variations')->insert($products->map(static fn ($product): array => [
                    'product_id' => $product->id,
                    'label' => 'Standard',
                    'vendor_price' => $product->vendor_price,
                    'selling_price' => $product->selling_price,
                    'sort_order' => 0,
                    'created_at' => $product->created_at ?? $now,
                    'updated_at' => $product->updated_at ?? $now,
                ])->all());
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
