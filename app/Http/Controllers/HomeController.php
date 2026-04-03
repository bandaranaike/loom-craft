<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductColor;
use App\Services\ProductPricingService;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function __invoke(): Response
    {
        $latestProducts = Product::query()
            ->with([
                'vendor',
                'media' => fn ($query) => $query->orderBy('sort_order'),
                'categories',
                'colors' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->inRandomOrder()
            ->limit(6)
            ->get()
            ->map(function (Product $product): array {
                $image = $product->media->firstWhere('type', 'image');
                $pricing = $this->productPricingService->forProduct($product);

                return [
                    'id' => $product->id,
                    'slug' => $product->resolveSlug(),
                    'name' => $product->name,
                    'original_price' => $pricing->originalPrice,
                    'selling_price' => $pricing->discountedPrice,
                    'effective_discount_percentage' => $pricing->effectiveDiscountPercentage,
                    'has_discount' => $pricing->hasDiscount,
                    'vendor_name' => $product->vendor?->display_name ?? 'Unknown vendor',
                    'vendor_slug' => $product->vendor?->slug,
                    'vendor_location' => $product->vendor?->location,
                    'image_url' => $image ? asset('storage/'.$image->path) : null,
                    'categories' => $product->categories
                        ->map(static fn ($category): array => [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        ])
                        ->values()
                        ->all(),
                    'colors' => $product->colors
                        ->map(static fn (ProductColor $color): array => [
                            'id' => $color->id,
                            'name' => $color->name,
                            'slug' => $color->slug,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'latest_products' => $latestProducts,
        ]);
    }
}
