<?php

namespace App\Http\Controllers;

use App\Actions\Order\ShowOrderConfirmation;
use App\DTOs\Order\OrderConfirmationData;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderConfirmationController extends Controller
{
    public function show(
        Request $request,
        Order $order,
        ShowOrderConfirmation $action,
    ): Response {
        $result = $action->handle(OrderConfirmationData::fromRequest($request, $order));

        return Inertia::render('orders/confirmation', [
            'order' => $result->toArray(),
        ]);
    }
}
