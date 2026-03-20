<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Order\ListAdminOrders;
use App\Actions\Order\ShowAdminOrder;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOfflineOrderRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    public function show(Order $order, ShowAdminOrder $action): Response
    {
        $result = $action->handle($order->id);

        return Inertia::render('admin/orders/show', [
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

        return back()->with('status', 'Order status updated.');
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

    public function destroy(Order $order): RedirectResponse
    {
        Gate::authorize('delete', $order);

        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('status', 'Order deleted.');
    }
}
