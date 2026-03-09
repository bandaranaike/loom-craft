<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductDimensions;
use App\DTOs\Product\ProductMediaItem;
use App\DTOs\Product\ProductShowData;
use App\DTOs\Product\ProductShowItem;
use App\DTOs\Product\ProductShowResult;
use App\DTOs\Product\ProductVendorSummary;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ShowPublicProduct
{
    public function handle(ProductShowData $data): ProductShowResult
    {
        Gate::authorize('viewPublicAny', Product::class);

        $product = Product::query()
            ->with([
                'vendor',
                'media' => fn ($query) => $query->orderBy('sort_order'),
                'categories' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
                'colors' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->findOrFail($data->productId);

        $images = $product->media
            ->where('type', 'image')
            ->map(
                static fn ($media) => new ProductMediaItem(
                    'image',
                    Storage::disk('public')->url($media->path),
                    $media->alt_text,
                )
            )
            ->values()
            ->all();

        $video = $product->media->firstWhere('type', 'video');
        $vendor = $product->vendor;

        if ($vendor === null) {
            throw new \RuntimeException('Product vendor is missing.');
        }

        return new ProductShowResult(
            new ProductShowItem(
                $product->id,
                $product->name,
                $product->description,
                Money::fromString((string) $product->vendor_price)->amount,
                Money::fromString((string) $product->selling_price)->amount,
                number_format((float) $product->commission_rate, 2, '.', ''),
                $product->materials,
                $product->pieces_count,
                $product->production_time_days,
                new ProductDimensions(
                    $product->dimension_length !== null ? (float) $product->dimension_length : null,
                    $product->dimension_width !== null ? (float) $product->dimension_width : null,
                    $product->dimension_height !== null ? (float) $product->dimension_height : null,
                    $product->dimension_unit,
                ),
                new ProductVendorSummary(
                    $vendor->id,
                    $vendor->display_name,
                    $vendor->slug,
                    $vendor->location,
                ),
                $images,
                $product->categories
                    ->map(static fn (ProductCategory $category): array => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->values()
                    ->all(),
                $product->colors
                    ->map(static fn (ProductColor $color): array => [
                        'id' => $color->id,
                        'name' => $color->name,
                        'slug' => $color->slug,
                    ])
                    ->values()
                    ->all(),
                $video?->path,
            )
        );
    }
}
