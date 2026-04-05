<?php

use App\Http\Controllers\Api\V1\Admin\OrderStickerDataController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\NotificationRegistrationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::patch('orders/{order}/status', OrderStatusController::class);
        Route::post('notifications/register', NotificationRegistrationController::class);
        Route::get('admin/orders/{order}/sticker-data', OrderStickerDataController::class);
    });
});
