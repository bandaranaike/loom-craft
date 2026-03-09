<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorInquiryRequest;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorContactSubmission;
use App\Services\ProductPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VendorPublicController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function show(Vendor $vendor): Response
    {
        abort_if($vendor->status !== 'approved', 404);

        $vendor->load([
            'locations' => fn ($query) => $query
                ->orderByDesc('is_primary')
                ->orderBy('location_name'),
            'products' => fn ($query) => $query
                ->where('status', 'active')
                ->with([
                    'media' => fn ($mediaQuery) => $mediaQuery->orderBy('sort_order'),
                    'categories' => fn ($categoryQuery) => $categoryQuery
                        ->where('is_active', true),
                    'colors' => fn ($colorQuery) => $colorQuery
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name'),
                ])
                ->latest(),
        ]);

        $products = $vendor->products->map(function (Product $product) use ($vendor): array {
            $image = $product->media->firstWhere('type', 'image');
            $pricing = $this->productPricingService->forProduct($product);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => str($product->description)->limit(120)->toString(),
                'original_price' => $pricing->originalPrice,
                'selling_price' => $pricing->discountedPrice,
                'effective_discount_percentage' => $pricing->effectiveDiscountPercentage,
                'has_discount' => $pricing->hasDiscount,
                'vendor_name' => $vendor->display_name,
                'vendor_slug' => $vendor->slug,
                'vendor_location' => $vendor->location,
                'image_url' => $image ? Storage::disk('public')->url($image->path) : null,
                'categories' => $product->categories
                    ->map(fn ($category): array => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->values()
                    ->all(),
                'colors' => $product->colors
                    ->map(fn ($color): array => [
                        'id' => $color->id,
                        'name' => $color->name,
                        'slug' => $color->slug,
                    ])
                    ->values()
                    ->all(),
            ];
        })->values();

        $categories = $products
            ->flatMap(fn (array $product): array => $product['categories'])
            ->groupBy('id')
            ->map(function ($group): array {
                $first = $group->first();

                return [
                    'id' => $first['id'],
                    'name' => $first['name'],
                    'slug' => $first['slug'],
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('name')
            ->values()
            ->all();

        return Inertia::render('vendors/show', [
            'vendor' => [
                'id' => $vendor->id,
                'display_name' => $vendor->display_name,
                'slug' => $vendor->slug,
                'tagline' => $vendor->tagline,
                'bio' => $vendor->bio,
                'about_title' => $vendor->about_title,
                'website_url' => $vendor->is_website_public ? $vendor->website_url : null,
                'contact_email' => $vendor->is_contact_public ? $vendor->contact_email : null,
                'contact_phone' => $vendor->is_contact_public ? $vendor->contact_phone : null,
                'whatsapp_number' => $vendor->is_contact_public ? $vendor->whatsapp_number : null,
                'location' => $vendor->location,
                'years_active' => $vendor->years_active,
                'craft_specialties' => $vendor->craft_specialties ?? [],
                'is_contact_public' => $vendor->is_contact_public,
                'is_website_public' => $vendor->is_website_public,
                'logo_url' => $vendor->logo_path
                    ? Storage::disk('public')->url($vendor->logo_path)
                    : null,
                'cover_image_url' => $vendor->cover_image_path
                    ? Storage::disk('public')->url($vendor->cover_image_path)
                    : null,
                'media' => collect([
                    $vendor->cover_image_path
                        ? [
                            'id' => 'cover-image',
                            'type' => 'image',
                            'label' => 'Cover image',
                            'url' => Storage::disk('public')->url($vendor->cover_image_path),
                        ]
                        : null,
                    $vendor->logo_path
                        ? [
                            'id' => 'logo-image',
                            'type' => 'image',
                            'label' => 'Vendor logo',
                            'url' => Storage::disk('public')->url($vendor->logo_path),
                        ]
                        : null,
                ])->filter()->values()->all(),
                'locations' => $vendor->locations
                    ->map(fn ($location): array => [
                        'id' => $location->id,
                        'location_name' => $location->location_name,
                        'address_line_1' => $location->address_line_1,
                        'address_line_2' => $location->address_line_2,
                        'city' => $location->city,
                        'region' => $location->region,
                        'postal_code' => $location->postal_code,
                        'country' => $location->country,
                        'phone' => $location->phone,
                        'hours' => $location->hours,
                        'map_url' => $location->map_url,
                        'is_primary' => $location->is_primary,
                    ])
                    ->values()
                    ->all(),
            ],
            'products' => $products->all(),
            'categories' => $categories,
            'status' => session('status'),
        ]);
    }

    public function storeInquiry(
        StoreVendorInquiryRequest $request,
        Vendor $vendor,
    ): RedirectResponse {
        abort_if($vendor->status !== 'approved', 404);

        VendorContactSubmission::query()->create([
            'vendor_id' => $vendor->id,
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString() ?: null,
            'subject' => $request->string('subject')->toString(),
            'message' => $request->string('message')->toString(),
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return back()->with('status', 'Your inquiry has been sent to the vendor.');
    }
}
