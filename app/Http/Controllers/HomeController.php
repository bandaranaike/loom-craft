<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\Suggestion;
use App\Models\User;
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
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (Product $product): array {
                $image = $product->media->firstWhere('type', 'image');
                $pricing = $this->productPricingService->forProduct($product);

                return [
                    'id' => $product->id,
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

        $approvedFeedback = Suggestion::query()
            ->with(['user.vendor'])
            ->where('status', 'approved')
            ->latest()
            ->limit(3)
            ->get()
            ->map(function (Suggestion $suggestion): array {
                $author = $suggestion->user;

                return [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'details' => $suggestion->details,
                    'author_name' => $author?->vendor?->display_name
                        ?? $author?->name
                        ?? 'Verified member',
                    'author_vendor_slug' => $author?->vendor?->slug,
                    'author_role' => $author?->role ?? 'customer',
                    'approved_at' => $suggestion->updated_at?->toDateString(),
                ];
            })
            ->values()
            ->all();

        /** @var User|null $user */
        $user = auth()->user();
        $myFeedback = null;

        if ($user !== null && in_array($user->role, ['vendor', 'customer'], true)) {
            $suggestion = Suggestion::query()
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            if ($suggestion !== null) {
                $myFeedback = [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'details' => $suggestion->details,
                    'status' => $suggestion->status,
                ];
            }
        }

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'atelier_ledger' => [
                'active_products' => Product::query()
                    ->where('status', 'active')
                    ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
                    ->count(),
                'approved_feedback' => Suggestion::query()
                    ->where('status', 'approved')
                    ->count(),
            ],
            'vendor_feedback' => $approvedFeedback,
            'latest_products' => $latestProducts,
            'my_feedback' => $myFeedback,
        ]);
    }
}
