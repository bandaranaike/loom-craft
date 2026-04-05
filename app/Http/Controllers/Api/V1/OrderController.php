<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AdminOrderDetailResource;
use App\Http\Resources\Api\V1\AdminOrderListResource;
use App\Http\Resources\Api\V1\VendorOrderDetailResource;
use App\Http\Resources\Api\V1\VendorOrderListResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();

        if ($user->role === 'admin') {
            Gate::authorize('viewAny', Order::class);

            $orders = Order::query()
                ->with('user')
                ->withCount('items')
                ->latest('placed_at')
                ->get();

            return response()->json(
                AdminOrderListResource::collection($orders)->resolve()
            );
        }

        Gate::authorize('viewVendorIndex', Order::class);

        $vendor = $user->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $orders = Order::query()
            ->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->withCount([
                'items as vendor_items_count' => fn ($query) => $query->where('vendor_id', $vendor->id),
            ])
            ->withSum([
                'items as vendor_items_total' => fn ($query) => $query->where('vendor_id', $vendor->id),
            ], 'line_total')
            ->latest('placed_at')
            ->get();

        return response()->json(
            VendorOrderListResource::collection($orders)->resolve()
        );
    }

    public function show(Order $order): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();

        if ($user->role === 'admin') {
            Gate::authorize('viewAny', Order::class);

            $order->load(['user', 'payment', 'addresses', 'shipments', 'items.product', 'items.vendor']);

            return response()->json(new AdminOrderDetailResource($order));
        }

        Gate::authorize('viewVendor', $order);

        $vendor = $user->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $order->load([
            'items' => fn ($query) => $query->where('vendor_id', $vendor->id)->with(['product']),
        ]);

        return response()->json(new VendorOrderDetailResource($order));
    }
}
