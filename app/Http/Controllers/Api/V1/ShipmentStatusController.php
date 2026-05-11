<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateShipmentStatusRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Http\JsonResponse;

class ShipmentStatusController extends Controller
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

    public function __invoke(UpdateShipmentStatusRequest $request, Order $order, Shipment $shipment): JsonResponse
    {
        $this->fulfillmentStatusService->updateShipmentStatus(
            $order,
            $shipment,
            $request->validated('status'),
            $request->user(),
        );

        return response()->json([
            'message' => 'Shipment status updated.',
            'shipment' => [
                'id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'status' => $shipment->fresh()->status,
            ],
            'order' => [
                'id' => $order->id,
                'public_id' => $order->public_id,
                'status' => $order->fresh()->status,
            ],
        ]);
    }
}
