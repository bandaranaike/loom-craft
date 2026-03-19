<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Order\ListAdminOrders;
use App\Actions\Order\ShowAdminOrder;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOfflineOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request, ListAdminOrders $action): Response
    {
        $result = $action->handle(OrderIndexData::fromRequest($request));

        return Inertia::render('admin/orders/index', [
            ...$result->toArray(),
        ]);
    }

    public function show(int $order, ShowAdminOrder $action): Response
    {
        $result = $action->handle($order);

        return Inertia::render('admin/orders/show', [
            'order' => $result->toArray(),
        ]);
    }

    public function updateOffline(
        UpdateOfflineOrderRequest $request,
        Order $order,
    ): RedirectResponse {
        $payment = $order->payment;

        if ($payment === null) {
            abort(404);
        }

        $validated = $request->validated();

        $order->update([
            'status' => $validated['order_status'],
            'shipping_responsibility' => 'platform',
        ]);

        $payment->update([
            'status' => $validated['payment_status'],
            'verified_by' => $request->user()?->id,
            'verified_at' => now(),
        ]);

        return back()->with('status', 'Offline order statuses updated.');
    }
}
