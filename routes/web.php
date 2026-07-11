<?php

use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\ContactSubmissionController as AdminContactSubmissionController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderReturnController as AdminOrderReturnController;
use App\Http\Controllers\Admin\OrderShipmentLabelController as AdminOrderShipmentLabelController;
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Admin\ProductColorController as AdminProductColorController;
use App\Http\Controllers\Admin\ShippingCarrierController as AdminShippingCarrierController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\VendorInquiryController as AdminVendorInquiryController;
use App\Http\Controllers\Admin\YouTubeAuthorizationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutPayPalController;
use App\Http\Controllers\CheckoutStripeController;
use App\Http\Controllers\ConnectedDeviceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoomWeaveDemoController;
use App\Http\Controllers\OrderBankTransferSlipController;
use App\Http\Controllers\OrderConfirmationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductIndexController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\Vendor\FeedbackController as VendorFeedbackController;
use App\Http\Controllers\Vendor\InquiryController as VendorInquiryController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\VendorProfileController;
use App\Http\Controllers\Vendor\VendorRegistrationController;
use App\Http\Controllers\VendorPublicController;
use App\Models\Product;
use App\Models\Vendor;
use App\Support\Site;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');
Route::get('loom-weave-demo', LoomWeaveDemoController::class)->name('loom-weave-demo');
Route::get('contact-us', [ContactController::class, 'show'])->name('contact.show');
Route::post('contact-us', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.store');
Route::get('privacy-policy', fn () => Inertia::render('privacy-policy'))
    ->name('privacy-policy');
Route::get('terms-of-service', fn () => Inertia::render('terms-of-service'))
    ->name('terms-of-service');
Route::get('manage-plans', fn () => Inertia::render('manage-plans'))
    ->name('plans.manage');
Route::get('robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Disallow: /dashboard',
        'Disallow: /vendor/',
        'Disallow: /checkout',
        'Disallow: /cart',
        'Disallow: /orders/',
        'Disallow: /connected-devices',
        'Disallow: /settings',
        'Sitemap: '.route('sitemap.xml'),
        '',
    ]);

    return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots.txt');
Route::get('sitemap.xml', function () {
    $today = now()->toDateString();

    $pages = [
        [
            'loc' => route('home'),
            'lastmod' => $today,
            'images' => [],
        ],
        [
            'loc' => route('products.index'),
            'lastmod' => $today,
            'images' => [],
        ],
        [
            'loc' => route('contact.show'),
            'lastmod' => $today,
            'images' => [],
        ],
        [
            'loc' => route('privacy-policy'),
            'lastmod' => $today,
            'images' => [],
        ],
        [
            'loc' => route('terms-of-service'),
            'lastmod' => $today,
            'images' => [],
        ],
    ];

    if (! Site::hidesLoomFeatures()) {
        $pages[] = [
            'loc' => route('loom-weave-demo'),
            'lastmod' => $today,
            'images' => [],
        ];
    }

    $productEntries = Product::query()
        ->where('status', 'active')
        ->with([
            'media' => fn ($query) => $query
                ->where('type', 'image')
                ->orderBy('sort_order')
                ->orderBy('id'),
        ])
        ->orderByDesc('updated_at')
        ->get(['id', 'name', 'slug', 'updated_at'])
        ->map(function (Product $product) use ($today): array {
            return [
                'loc' => route('products.show', $product),
                'lastmod' => optional($product->updated_at)->toDateString() ?? $today,
                'images' => $product->media
                    ->map(static fn ($media): array => [
                        'loc' => Storage::disk('public')->url($media->path),
                        'title' => $product->name,
                        'caption' => $media->alt_text,
                    ])
                    ->values()
                    ->all(),
            ];
        })
        ->all();

    $vendorEntries = Vendor::query()
        ->where('status', 'approved')
        ->orderByDesc('updated_at')
        ->get(['display_name', 'slug', 'logo_path', 'cover_image_path', 'updated_at'])
        ->map(function (Vendor $vendor) use ($today): array {
            return [
                'loc' => route('vendors.show', $vendor),
                'lastmod' => optional($vendor->updated_at)->toDateString() ?? $today,
                'images' => collect([
                    $vendor->cover_image_path !== null
                        ? [
                            'loc' => Storage::disk('public')->url($vendor->cover_image_path),
                            'title' => $vendor->display_name,
                            'caption' => 'Cover image',
                        ]
                        : null,
                    $vendor->logo_path !== null
                        ? [
                            'loc' => Storage::disk('public')->url($vendor->logo_path),
                            'title' => $vendor->display_name,
                            'caption' => 'Vendor logo',
                        ]
                        : null,
                ])
                    ->filter()
                    ->values()
                    ->all(),
            ];
        })
        ->all();

    $entries = array_merge($pages, $productEntries, $vendorEntries);
    $hasImageEntries = collect($entries)->contains(
        fn (array $entry): bool => is_array($entry['images'] ?? null) && $entry['images'] !== []
    );

    $imageNamespace = $hasImageEntries
        ? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
        : '';

    $xmlEntries = collect($entries)->map(static function (array $entry): string {
        $loc = htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $lastmod = isset($entry['lastmod']) && is_string($entry['lastmod']) ? sprintf('<lastmod>%s</lastmod>', htmlspecialchars($entry['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8')) : '';
        $images = collect($entry['images'] ?? [])
            ->map(static function (array $image): string {
                $imageLines = [
                    sprintf(
                        '<image:loc>%s</image:loc>',
                        htmlspecialchars($image['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')
                    ),
                ];

                if (isset($image['title']) && is_string($image['title']) && $image['title'] !== '') {
                    $imageLines[] = sprintf(
                        '<image:title>%s</image:title>',
                        htmlspecialchars($image['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8')
                    );
                }

                if (isset($image['caption']) && is_string($image['caption']) && $image['caption'] !== '') {
                    $imageLines[] = sprintf(
                        '<image:caption>%s</image:caption>',
                        htmlspecialchars($image['caption'], ENT_XML1 | ENT_QUOTES, 'UTF-8')
                    );
                }

                return "<image:image>\n      ".implode("\n      ", $imageLines)."\n    </image:image>";
            })
            ->implode("\n");

        $imageBlock = $images !== '' ? "\n{$images}" : '';

        return <<<XML
  <url>
    <loc>{$loc}</loc>{$lastmod}{$imageBlock}
  </url>
XML;
    })->implode("\n");

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"{$imageNamespace}>
{$xmlEntries}
</urlset>
XML;

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap.xml');
Route::redirect('autopay/cancel', '/manage-plans')
    ->name('autopay.cancel');

Route::get('products', [ProductIndexController::class, 'index'])
    ->name('products.index');
Route::get('product/{product:slug}', [ProductShowController::class, 'show'])
    ->name('products.show');
Route::get('vendors/{vendor:slug}', [VendorPublicController::class, 'show'])
    ->name('vendors.show');
Route::post('vendors/{vendor:slug}/inquiries', [VendorPublicController::class, 'storeInquiry'])
    ->middleware('throttle:10,1')
    ->name('vendors.inquiries.store');

Route::get('cart', [CartController::class, 'show'])
    ->name('cart.show');
Route::post('cart/items', [CartItemController::class, 'store'])
    ->name('cart.items.store');
Route::patch('cart/items/{cartItem}', [CartItemController::class, 'update'])
    ->name('cart.items.update');
Route::delete('cart/items/{cartItem}', [CartItemController::class, 'destroy'])
    ->name('cart.items.destroy');

Route::get('checkout', [CheckoutController::class, 'show'])
    ->name('checkout.show');
Route::post('checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');
Route::post('checkout/paypal/create', [CheckoutPayPalController::class, 'create'])
    ->name('checkout.paypal.create');
Route::post('checkout/paypal/card/create', [CheckoutPayPalController::class, 'createCard'])
    ->name('checkout.paypal.card.create');
Route::post('checkout/paypal/card/capture', [CheckoutPayPalController::class, 'captureCard'])
    ->name('checkout.paypal.card.capture');
Route::get('checkout/paypal/success', [CheckoutPayPalController::class, 'success'])
    ->name('checkout.paypal.success');
Route::get('checkout/paypal/approved', [CheckoutPayPalController::class, 'approved'])
    ->name('checkout.paypal.approved');
Route::get('checkout/paypal/cancelled', [CheckoutPayPalController::class, 'cancelled'])
    ->name('checkout.paypal.cancelled');
Route::post('checkout/stripe/create', [CheckoutStripeController::class, 'create'])
    ->name('checkout.stripe.create');
Route::get('checkout/stripe/approved', [CheckoutStripeController::class, 'approved'])
    ->name('checkout.stripe.approved');
Route::get('checkout/stripe/cancelled', [CheckoutStripeController::class, 'cancelled'])
    ->name('checkout.stripe.cancelled');

Route::get('orders/{order:public_id}/confirmation', [OrderConfirmationController::class, 'show'])
    ->name('orders.confirmation');
Route::get('orders/{order:public_id}', [OrderController::class, 'show'])
    ->name('orders.show');
Route::post('orders/{order:public_id}/bank-transfer-slip', [OrderBankTransferSlipController::class, 'store'])
    ->name('orders.bank-transfer-slip.store');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('connected-devices', [ConnectedDeviceController::class, 'index'])
        ->name('connected-devices.index');
    Route::delete('connected-devices/{token}', [ConnectedDeviceController::class, 'destroy'])
        ->name('connected-devices.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::post('product/{product:slug}/reviews', [ProductReviewController::class, 'store'])
        ->name('products.reviews.store');

    Route::get('vendor/register', [VendorRegistrationController::class, 'register'])
        ->name('vendor.register');
    Route::post('vendor/register', [VendorRegistrationController::class, 'store'])
        ->name('vendor.register.store');

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('profile', [VendorProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('profile', [VendorProfileController::class, 'update'])
            ->name('profile.update');
        Route::get('products', [ProductController::class, 'index'])
            ->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])
            ->name('products.create');
        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');
        Route::patch('products/{product}', [ProductController::class, 'update'])
            ->name('products.update');
        Route::post('products/{product}/images', [ProductController::class, 'storeImages'])
            ->name('products.images.store');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])
            ->name('products.images.destroy');
        Route::get('orders', [VendorOrderController::class, 'index'])
            ->name('orders.index');
        Route::get('orders/{order}', [VendorOrderController::class, 'show'])
            ->name('orders.show');
        Route::patch('orders/{order}/shipments/{shipment}/status', [VendorOrderController::class, 'updateShipmentStatus'])
            ->name('orders.shipments.status.update');
        Route::get('inquiries', [VendorInquiryController::class, 'index'])
            ->name('inquiries.index');
        Route::get('feedback', [VendorFeedbackController::class, 'create'])
            ->name('feedback.create');
        Route::post('feedback', [VendorFeedbackController::class, 'store'])
            ->name('feedback.store');
    });

    Route::prefix('admin')->group(function () {
        Route::get('contact-submissions', [AdminContactSubmissionController::class, 'index'])
            ->name('admin.contact-submissions.index');
        Route::patch('contact-submissions/{contactSubmission}/status', [AdminContactSubmissionController::class, 'updateStatus'])
            ->name('admin.contact-submissions.status.update');
        Route::post('contact-submissions/{contactSubmission}/reply', [AdminContactSubmissionController::class, 'reply'])
            ->name('admin.contact-submissions.reply');

        Route::get('vendors/pending', [VendorApprovalController::class, 'pending'])
            ->name('admin.vendors.pending');
        Route::post('vendors/{vendor}/approve', [VendorApprovalController::class, 'approve'])
            ->name('admin.vendors.approve');
        Route::post('vendors/{vendor}/reject', [VendorApprovalController::class, 'reject'])
            ->name('admin.vendors.reject');

        Route::get('youtube/connect', [YouTubeAuthorizationController::class, 'connect'])
            ->name('admin.youtube.connect');
        Route::get('youtube/callback', [YouTubeAuthorizationController::class, 'callback'])
            ->name('admin.youtube.callback');

        Route::get('orders', [AdminOrderController::class, 'index'])
            ->name('admin.orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])
            ->name('admin.orders.show');
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('admin.orders.status.update');
        Route::patch('orders/{order}/shipments/{shipment}/status', [AdminOrderController::class, 'updateShipmentStatus'])
            ->name('admin.orders.shipments.status.update');
        Route::patch('orders/{order}/shipments/{shipment}/tracking', [AdminOrderController::class, 'updateShipmentTracking'])
            ->name('admin.orders.shipments.tracking.update');
        Route::patch('orders/{order}/shipments/{shipment}/delivery-evidence', [AdminOrderController::class, 'updateShipmentDeliveryEvidence'])
            ->name('admin.orders.shipments.delivery-evidence.update');
        Route::post('orders/{order}/returns', [AdminOrderReturnController::class, 'store'])
            ->name('admin.orders.returns.store');
        Route::patch('orders/{order}/returns/{orderReturn}/status', [AdminOrderReturnController::class, 'updateStatus'])
            ->name('admin.orders.returns.status.update');
        Route::patch('orders/{order}/returns/{orderReturn}/tracking', [AdminOrderReturnController::class, 'updateTracking'])
            ->name('admin.orders.returns.tracking.update');
        Route::post('orders/{order}/complaints', [AdminComplaintController::class, 'store'])
            ->name('admin.orders.complaints.store');
        Route::patch('complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus'])
            ->name('admin.complaints.status.update');
        Route::get('orders/{order}/shipments/{shipment}/label', AdminOrderShipmentLabelController::class)
            ->name('admin.orders.shipments.label.show');
        Route::get('orders/{order}/shipments/{shipment}/label.pdf', [AdminOrderShipmentLabelController::class, 'download'])
            ->name('admin.orders.shipments.label.download');
        Route::patch('orders/{order}/offline', [AdminOrderController::class, 'updateOffline'])
            ->name('admin.orders.offline.update');
        Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])
            ->name('admin.orders.destroy');

        Route::get('feedback/pending', [AdminFeedbackController::class, 'pending'])
            ->name('admin.feedback.pending');
        Route::post('feedback/{suggestion}/approve', [AdminFeedbackController::class, 'approve'])
            ->name('admin.feedback.approve');

        Route::get('products/pending', [ProductApprovalController::class, 'pending'])
            ->name('admin.products.pending');
        Route::post('products/{product}/approve', [ProductApprovalController::class, 'approve'])
            ->name('admin.products.approve');
        Route::get('product-categories', [AdminProductCategoryController::class, 'index'])
            ->name('admin.product-categories.index');
        Route::post('product-categories', [AdminProductCategoryController::class, 'store'])
            ->name('admin.product-categories.store');
        Route::patch('product-categories/{productCategory}', [AdminProductCategoryController::class, 'update'])
            ->name('admin.product-categories.update');
        Route::get('product-colors', [AdminProductColorController::class, 'index'])
            ->name('admin.product-colors.index');
        Route::post('product-colors', [AdminProductColorController::class, 'store'])
            ->name('admin.product-colors.store');
        Route::patch('product-colors/{productColor}', [AdminProductColorController::class, 'update'])
            ->name('admin.product-colors.update');
        Route::delete('product-colors/{productColor}', [AdminProductColorController::class, 'destroy'])
            ->name('admin.product-colors.destroy');
        Route::get('shipping-carriers', [AdminShippingCarrierController::class, 'index'])
            ->name('admin.shipping-carriers.index');
        Route::post('shipping-carriers', [AdminShippingCarrierController::class, 'store'])
            ->name('admin.shipping-carriers.store');
        Route::patch('shipping-carriers/{shippingCarrier}', [AdminShippingCarrierController::class, 'update'])
            ->name('admin.shipping-carriers.update');
        Route::delete('shipping-carriers/{shippingCarrier}', [AdminShippingCarrierController::class, 'destroy'])
            ->name('admin.shipping-carriers.destroy');
        Route::post('shipping-carriers/{shippingCarrier}/services', [AdminShippingCarrierController::class, 'storeService'])
            ->name('admin.shipping-carriers.services.store');
        Route::patch('shipping-carriers/{shippingCarrier}/services/{shippingService}', [AdminShippingCarrierController::class, 'updateService'])
            ->name('admin.shipping-carriers.services.update');
        Route::delete('shipping-carriers/{shippingCarrier}/services/{shippingService}', [AdminShippingCarrierController::class, 'destroyService'])
            ->name('admin.shipping-carriers.services.destroy');
        Route::get('vendor-inquiries/pending', [AdminVendorInquiryController::class, 'pending'])
            ->name('admin.vendor-inquiries.pending');
        Route::post('vendor-inquiries/{inquiry}/approve', [AdminVendorInquiryController::class, 'approve'])
            ->name('admin.vendor-inquiries.approve');
        Route::post('vendor-inquiries/{inquiry}/reject', [AdminVendorInquiryController::class, 'reject'])
            ->name('admin.vendor-inquiries.reject');
    });
    Route::get('orders', [OrderController::class, 'index'])
        ->name('orders.index');
});

require __DIR__.'/settings.php';
