<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Order\ListVendorOrders;
use App\Actions\Order\ShowVendorOrder;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateOfflineOrderRequest;
use App\Http\Requests\Vendor\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request, ListVendorOrders $action): Response
    {
        $result = $action->handle(OrderIndexData::fromRequest($request));

        return Inertia::render('vendor/orders/index', [
            ...$result->toArray(),
        ]);
    }

    public function show(Request $request, Order $order, ShowVendorOrder $action): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $result = $action->handle($user, $order);

        return Inertia::render('vendor/orders/show', [
            'order' => $result->toArray(),
        ]);
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
    ): RedirectResponse {
        $order->update([
            'status' => $request->validated('order_status'),
        ]);

        return back()->with('status', 'Order marked as shipped.');
    }

    public function updateOffline(
        UpdateOfflineOrderRequest $request,
        Order $order,
    ): RedirectResponse {
        $payment = $order->payment;

        if ($payment === null) {
            abort(404);
        }

        $payment->update([
            'status' => $request->validated('payment_status'),
            'verified_by' => $request->user()?->id,
            'verified_at' => now(),
        ]);

        return back()->with('status', 'Offline payment status updated.');
    }
}
