<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Order\ListVendorOrders;
use App\Actions\Order\ShowVendorOrder;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateOfflineOrderRequest;
use App\Http\Requests\Vendor\UpdateShipmentStatusRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

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
