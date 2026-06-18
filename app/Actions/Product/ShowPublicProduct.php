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
use App\Models\ProductReview;
use App\Services\ProductPricingService;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ShowPublicProduct
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function handle(ProductShowData $data): ProductShowResult
    {
        Gate::authorize('viewPublicAny', Product::class);

        $product = Product::query()
            ->with([
                'vendor',
                'variations',
                'media' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'categories' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
                'colors' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
                'reviews' => fn ($query) => $query
                    ->with('user:id,name')
                    ->latest(),
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->findOrFail($data->product->id);

        $images = $product->media
            ->where('type', 'image')
            ->map(
                static fn ($media) => new ProductMediaItem(
                    $media->id,
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

        $pricing = $this->productPricingService->forProduct($product);
        $defaultVariation = $product->variations->first();
        $reviewSummary = [
            'average_rating' => $product->reviews_avg_rating !== null
                ? number_format((float) $product->reviews_avg_rating, 1, '.', '')
                : null,
            'total_reviews' => $product->reviews_count,
        ];
        $reviews = $product->reviews
            ->map(static fn (ProductReview $review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'review' => $review->review,
                'reviewer_name' => $review->user?->name ?? 'Verified customer',
                'created_at' => $review->created_at?->toDateString(),
                'created_at_human' => $review->created_at?->format('M j, Y'),
            ])
            ->values()
            ->all();
        $hasDeliveredPurchase = $data->user?->exists
            ? $product->hasDeliveredPurchaseBy($data->user)
            : false;
        $hasReview = $data->user?->exists
            ? $product->hasReviewBy($data->user)
            : false;
        $canSubmitReview = $data->user?->exists
            ? $hasDeliveredPurchase && ! $hasReview
            : false;
        $reviewForm = [
            'can_submit' => $canSubmitReview,
            'has_delivered_purchase' => $hasDeliveredPurchase,
            'has_reviewed' => $hasReview,
            'requires_authentication' => $data->user === null,
            'message' => $data->user === null
                ? 'Sign in after delivery to leave a review.'
                : ($hasReview
                    ? 'You already shared feedback for this product.'
                    : ($hasDeliveredPurchase
                        ? null
                        : 'Reviews open once your order is fulfilled.')),
        ];

        return new ProductShowResult(
            new ProductShowItem(
                $product->id,
                $product->resolveSlug(),
                $product->resolveProductCode(),
                $product->name,
                $product->description,
                Money::fromString((string) $product->vendor_price)->amount,
                $pricing->originalPrice,
                $pricing->discountedPrice,
                $pricing->effectiveDiscountPercentage,
                $pricing->hasDiscount,
                number_format((float) $product->commission_rate, 2, '.', ''),
                $product->materials,
                $product->pieces_count,
                $product->production_time_days,
                new ProductDimensions(
                    $defaultVariation?->dimension_length !== null ? (float) $defaultVariation->dimension_length : null,
                    $defaultVariation?->dimension_width !== null ? (float) $defaultVariation->dimension_width : null,
                    $defaultVariation?->dimension_height !== null ? (float) $defaultVariation->dimension_height : null,
                    $product->dimension_unit,
                ),
                new ProductVendorSummary(
                    $vendor->id,
                    $vendor->display_name,
                    $vendor->slug,
                    $vendor->location,
                    $vendor->contact_email,
                    $vendor->contact_phone,
                    $vendor->whatsapp_number,
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
                $product->variations
                    ->map(fn ($variation): array => [
                        'id' => $variation->id,
                        'label' => $variation->label,
                        'vendor_price' => Money::fromString((string) $variation->vendor_price)->amount,
                        'original_price' => $this->productPricingService->forVariation($product, $variation)->originalPrice,
                        'selling_price' => $this->productPricingService->forVariation($product, $variation)->discountedPrice,
                        'dimensions' => (new ProductDimensions(
                            $variation->dimension_length !== null ? (float) $variation->dimension_length : null,
                            $variation->dimension_width !== null ? (float) $variation->dimension_width : null,
                            $variation->dimension_height !== null ? (float) $variation->dimension_height : null,
                            $product->dimension_unit,
                        ))->toArray(),
                    ])
                    ->values()
                    ->all(),
                $video?->path,
            ),
            $reviewSummary,
            $reviews,
            $reviewForm,
        );
    }
}
