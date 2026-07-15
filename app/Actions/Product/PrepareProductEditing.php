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
            'variations',
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
            (string) config('commerce.commission_rate'),
            $data->user->vendor?->display_name,
            $data->user->vendor?->slug,
            [
                'id' => $product->id,
                'product_code' => $product->resolveProductCode(),
                'name' => $product->name,
                'description' => $product->description,
                'vendor_price' => (string) $product->vendor_price,
                'discount_percentage' => $product->discount_percentage !== null
                    ? number_format((float) $product->discount_percentage, 2, '.', '')
                    : null,
                'materials' => $product->materials,
                'pieces_count' => $product->pieces_count,
                'production_time_days' => $product->production_time_days,
                'expiry_information' => $product->expiry_information,
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
                'variations' => $product->variations
                    ->map(static fn ($variation): array => [
                        'id' => $variation->id,
                        'label' => $variation->label,
                        'vendor_price' => (string) $variation->vendor_price,
                        'dimension_length' => $variation->dimension_length !== null ? (float) $variation->dimension_length : null,
                        'dimension_width' => $variation->dimension_width !== null ? (float) $variation->dimension_width : null,
                        'dimension_height' => $variation->dimension_height !== null ? (float) $variation->dimension_height : null,
                    ])
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
