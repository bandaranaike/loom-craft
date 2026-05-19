<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderReturnRequest;
use App\Http\Requests\Admin\UpdateOrderReturnStatusRequest;
use App\Http\Requests\Admin\UpdateOrderReturnTrackingRequest;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Shipment;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class OrderReturnController extends Controller
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

    public function store(StoreOrderReturnRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('updateStatus', $order);

        $validated = $request->validated();
        $shipment = isset($validated['shipment_id'])
            ? Shipment::query()
                ->where('order_id', $order->id)
                ->findOrFail($validated['shipment_id'])
            : null;

        $this->fulfillmentStatusService->createReturnRequest(
            order: $order,
            actor: $request->user(),
            reason: $validated['reason'],
            items: $validated['items'],
            shipment: $shipment,
            customerNote: $validated['customer_note'] ?? null,
            adminNote: $validated['admin_note'] ?? null,
        );

        return back()->with('status', 'Return request recorded.');
    }

    public function updateStatus(
        UpdateOrderReturnStatusRequest $request,
        Order $order,
        OrderReturn $orderReturn,
    ): RedirectResponse {
        Gate::authorize('updateStatus', $order);

        if ($orderReturn->order_id !== $order->id) {
            abort(404);
        }

        $validated = $request->validated();

        $this->fulfillmentStatusService->updateReturnStatus(
            order: $order,
            orderReturn: $orderReturn,
            nextStatus: $validated['return_status'],
            actor: $request->user(),
            reason: $validated['reason'] ?? null,
            note: $validated['note'] ?? null,
            resolution: $validated['resolution'] ?? null,
        );

        return back()->with('status', 'Return status updated.');
    }

    public function updateTracking(
        UpdateOrderReturnTrackingRequest $request,
        Order $order,
        OrderReturn $orderReturn,
    ): RedirectResponse {
        Gate::authorize('updateStatus', $order);

        if ($orderReturn->order_id !== $order->id) {
            abort(404);
        }

        $this->fulfillmentStatusService->updateReturnTracking(
            order: $order,
            orderReturn: $orderReturn,
            actor: $request->user(),
            shippingCarrierId: $request->integer('shipping_carrier_id'),
            trackingNumber: $request->validated('tracking_number'),
            shippingServiceId: $request->integer('shipping_service_id') ?: null,
        );

        return back()->with('status', 'Return tracking updated.');
    }
}
