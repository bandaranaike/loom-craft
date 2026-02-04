<?php

use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Vendor\VendorRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

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

    Route::prefix('admin')->group(function () {
        Route::get('vendors/pending', [VendorApprovalController::class, 'pending'])
            ->name('admin.vendors.pending');
        Route::post('vendors/{vendor}/approve', [VendorApprovalController::class, 'approve'])
            ->name('admin.vendors.approve');
        Route::post('vendors/{vendor}/reject', [VendorApprovalController::class, 'reject'])
            ->name('admin.vendors.reject');
    });
});

require __DIR__.'/settings.php';
