<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
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
        $visibleProductQuery = static fn ($query) => $query
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'));

        $categorySections = ProductCategory::query()
            ->where('is_active', true)
            ->whereHas('products', $visibleProductQuery)
            ->with([
                'products' => fn ($query) => $visibleProductQuery($query)
                    ->with([
                        'vendor',
                        'media' => fn ($query) => $query->orderBy('sort_order'),
                        'categories',
                        'colors' => fn ($query) => $query
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name'),
                    ])
                    ->orderByDesc('products.created_at')
                    ->orderByDesc('products.id')
                    ->limit(3),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (ProductCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'products' => $category->products
                    ->map(fn (Product $product): array => $this->productCardPayload($product))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'category_sections' => $categorySections,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     slug: string,
     *     name: string,
     *     original_price: string,
     *     selling_price: string,
     *     effective_discount_percentage: string,
     *     has_discount: bool,
     *     vendor_name: string,
     *     vendor_slug: string|null,
     *     vendor_location: string|null,
     *     image_url: string|null,
     *     categories: list<array{id: int, name: string, slug: string}>,
     *     colors: list<array{id: int, name: string, slug: string}>
     * }
     */
    private function productCardPayload(Product $product): array
    {
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
                ->map(static fn (ProductCategory $category): array => [
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
    }
}
