<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('product_code');
        });

        DB::table('products')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    $baseSlug = Str::slug((string) $product->name);

                    if ($baseSlug === '') {
                        $baseSlug = 'product';
                    }

                    $slug = $baseSlug;
                    $suffix = 2;

                    while (
                        DB::table('products')
                            ->where('slug', $slug)
                            ->where('id', '!=', $product->id)
                            ->exists()
                    ) {
                        $slug = "{$baseSlug}-{$suffix}";
                        $suffix++;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'slug' => $slug,
                        ]);
                }
            });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
