<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\YouTubeAuthorizationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderConfirmationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductIndexController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\VendorRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('products', [ProductIndexController::class, 'index'])
    ->name('products.index');
Route::get('products/{product}', [ProductShowController::class, 'show'])
    ->name('products.show');

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

Route::get('orders/{order}/confirmation', [OrderConfirmationController::class, 'show'])
    ->name('orders.confirmation');

Route::get('dashboard', function () {
    return Inertia::render('dashboard', [
        'status' => session('status'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('vendor/register', [VendorRegistrationController::class, 'register'])
        ->name('vendor.register');
    Route::post('vendor/register', [VendorRegistrationController::class, 'store'])
        ->name('vendor.register.store');

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('products', [ProductController::class, 'index'])
            ->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])
            ->name('products.create');
        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store');
        Route::get('orders', [VendorOrderController::class, 'index'])
            ->name('orders.index');
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
    });

    Route::get('orders', [OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');
});

require __DIR__.'/settings.php';
