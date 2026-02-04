<?php

use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\YouTubeAuthorizationController;
use App\Http\Controllers\ProductIndexController;
use App\Http\Controllers\ProductShowController;
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
    });
});

require __DIR__.'/settings.php';
