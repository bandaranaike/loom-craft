<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

    public function __invoke(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->fulfillmentStatusService->updateOrderStatus(
            $order,
            $request->validated('status'),
            $request->user(),
        );

        return response()->json([
            'message' => 'Order status updated.',
            'order' => [
                'id' => $order->id,
                'public_id' => $order->public_id,
                'status' => $order->status,
            ],
        ]);
    }
}
