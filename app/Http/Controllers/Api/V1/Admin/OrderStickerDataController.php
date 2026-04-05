<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderStickerDataResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OrderStickerDataController extends Controller
{
    public function __invoke(Order $order): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);

        $order->load(['user', 'addresses', 'items.product', 'items.vendor']);

        return response()->json(new OrderStickerDataResource($order));
    }
}
