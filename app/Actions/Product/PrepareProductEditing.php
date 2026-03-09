<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductEditFormData;
use App\DTOs\Product\ProductEditFormResult;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PrepareProductEditing
{
    public function handle(ProductEditFormData $data): ProductEditFormResult
    {
        Gate::authorize('update', $data->product);

        $product = $data->product->load([
            'media' => fn ($query) => $query
                ->where('type', 'image')
                ->orderBy('sort_order'),
            'categories' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name'),
            'colors' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name'),
        ]);
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ProductCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->all();
        $colors = ProductColor::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ProductColor $color): array => [
                'id' => $color->id,
                'name' => $color->name,
                'slug' => $color->slug,
            ])
            ->all();

        return new ProductEditFormResult(
            '7.00',
            $data->user->vendor?->display_name,
            $data->user->vendor?->slug,
            [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'vendor_price' => (string) $product->vendor_price,
                'materials' => $product->materials,
                'pieces_count' => $product->pieces_count,
                'production_time_days' => $product->production_time_days,
                'dimension_length' => $product->dimension_length !== null ? (float) $product->dimension_length : null,
                'dimension_width' => $product->dimension_width !== null ? (float) $product->dimension_width : null,
                'dimension_height' => $product->dimension_height !== null ? (float) $product->dimension_height : null,
                'dimension_unit' => $product->dimension_unit,
                'category_ids' => $product->categories
                    ->pluck('id')
                    ->map(static fn (int|string $id): int => (int) $id)
                    ->values()
                    ->all(),
                'color_ids' => $product->colors
                    ->pluck('id')
                    ->map(static fn (int|string $id): int => (int) $id)
                    ->values()
                    ->all(),
                'images' => $product->media
                    ->map(static fn ($media): array => [
                        'id' => $media->id,
                        'url' => Storage::disk('public')->url($media->path),
                    ])
                    ->all(),
            ],
            $categories,
            $colors,
        );
    }
}
