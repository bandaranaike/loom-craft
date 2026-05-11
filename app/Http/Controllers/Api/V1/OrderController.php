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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user->role === 'admin') {
            return $this->adminIndexResponse($user);
        }

        return $this->vendorIndexResponse($user);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if ($user->role === 'admin') {
            return $this->adminShowResponse($user, $order);
        }

        return $this->vendorShowResponse($user, $order);
    }

    private function adminIndexResponse(User $user): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);
        abort_unless($user->tokenCan('orders:read'), 403);

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->latest('created_at')
            ->get();

        return response()->json(
            AdminOrderListResource::collection($orders)->resolve()
        );
    }

    private function vendorIndexResponse(User $user): JsonResponse
    {
        Gate::authorize('viewVendorIndex', Order::class);
        abort_unless($user->tokenCan('orders:read'), 403);

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
            ->latest('created_at')
            ->get();

        return response()->json(
            VendorOrderListResource::collection($orders)->resolve()
        );
    }

    private function adminShowResponse(User $user, Order $order): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);
        abort_unless($user->tokenCan('orders:read'), 403);

        $order->load([
            'user',
            'addresses',
            'shipments',
            'items.product.media' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'items.vendor',
        ]);

        return response()->json(new AdminOrderDetailResource($order));
    }

    private function vendorShowResponse(User $user, Order $order): JsonResponse
    {
        Gate::authorize('viewVendor', $order);
        abort_unless($user->tokenCan('orders:read'), 403);

        $vendor = $user->vendor;

        if ($vendor === null) {
            abort(403);
        }

        $order->load([
            'shipments' => fn ($query) => $query
                ->where(function ($shipmentQuery) use ($vendor): void {
                    $shipmentQuery
                        ->where('vendor_id', $vendor->id)
                        ->orWhereNull('vendor_id');
                })
                ->orderBy('id'),
            'items' => fn ($query) => $query
                ->where('vendor_id', $vendor->id)
                ->with([
                    'product.media' => fn ($mediaQuery) => $mediaQuery->orderBy('sort_order')->orderBy('id'),
                ]),
        ]);

        return response()->json(new VendorOrderDetailResource($order));
    }

    private function authenticatedUser(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
