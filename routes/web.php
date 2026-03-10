<?php

use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Admin\ProductColorController as AdminProductColorController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\VendorInquiryController as AdminVendorInquiryController;
use App\Http\Controllers\Admin\YouTubeAuthorizationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutPayPalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoomWeaveDemoController;
use App\Http\Controllers\OrderConfirmationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductIndexController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\Vendor\FeedbackController as VendorFeedbackController;
use App\Http\Controllers\Vendor\InquiryController as VendorInquiryController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\VendorProfileController;
use App\Http\Controllers\Vendor\VendorRegistrationController;
use App\Http\Controllers\VendorPublicController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');
Route::get('loom-weave-demo', LoomWeaveDemoController::class)->name('loom-weave-demo');
Route::get('privacy-policy', fn () => Inertia::render('privacy-policy'))
    ->name('privacy-policy');
Route::get('terms-of-service', fn () => Inertia::render('terms-of-service'))
    ->name('terms-of-service');

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
Route::get('checkout/paypal/approved', [CheckoutPayPalController::class, 'approved'])
    ->name('checkout.paypal.approved');
Route::get('checkout/paypal/cancelled', [CheckoutPayPalController::class, 'cancelled'])
    ->name('checkout.paypal.cancelled');

Route::get('orders/{order}/confirmation', [OrderConfirmationController::class, 'show'])
    ->name('orders.confirmation');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
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
        Route::get('inquiries', [VendorInquiryController::class, 'index'])
            ->name('inquiries.index');
        Route::get('feedback', [VendorFeedbackController::class, 'create'])
            ->name('feedback.create');
        Route::post('feedback', [VendorFeedbackController::class, 'store'])
            ->name('feedback.store');
    });

    Route::prefix('admin')->group(function () {
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
        Route::get('vendor-inquiries/pending', [AdminVendorInquiryController::class, 'pending'])
            ->name('admin.vendor-inquiries.pending');
        Route::post('vendor-inquiries/{inquiry}/approve', [AdminVendorInquiryController::class, 'approve'])
            ->name('admin.vendor-inquiries.approve');
        Route::post('vendor-inquiries/{inquiry}/reject', [AdminVendorInquiryController::class, 'reject'])
            ->name('admin.vendor-inquiries.reject');
    });

    Route::get('orders', [OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');
});

require __DIR__.'/settings.php';
