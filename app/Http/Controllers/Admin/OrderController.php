<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Order\ListAdminOrders;
use App\Actions\Order\ShowAdminOrder;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOfflineOrderRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdateShipmentStatusRequest;
use App\Http\Requests\Admin\UpdateShipmentTrackingRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

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
        $this->fulfillmentStatusService->updateOrderStatus(
            $order,
            $request->validated('order_status'),
            $request->user(),
        );

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

        $this->fulfillmentStatusService->updatePaymentStatus(
            $order,
            $payment,
            $request->validated('payment_status'),
            $request->user(),
        );

        return back()->with('status', 'Offline payment status updated.');
    }

    public function updateShipmentStatus(
        UpdateShipmentStatusRequest $request,
        Order $order,
        Shipment $shipment,
    ): RedirectResponse {
        $this->fulfillmentStatusService->updateShipmentStatus(
            $order,
            $shipment,
            $request->validated('shipment_status'),
            $request->user(),
        );

        return back()->with('status', 'Shipment status updated.');
    }

    public function updateShipmentTracking(
        UpdateShipmentTrackingRequest $request,
        Order $order,
        Shipment $shipment,
    ): RedirectResponse {
        $this->fulfillmentStatusService->updateShipmentTracking(
            $order,
            $shipment,
            $request->user(),
            $request->validated('carrier'),
            $request->validated('tracking_number'),
            $request->validated('service_level'),
        );

        return back()->with('status', 'Shipment tracking updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        Gate::authorize('delete', $order);

        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('status', 'Order deleted.');
    }
}
