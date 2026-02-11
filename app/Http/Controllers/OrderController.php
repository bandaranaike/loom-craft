<?php

namespace App\Http\Controllers;

use App\Actions\Order\ListOrders;
use App\Actions\Order\ShowOrder;
use App\DTOs\Order\OrderIndexData;
use App\DTOs\Order\OrderShowData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request, ListOrders $action): Response
    {
        $result = $action->handle(OrderIndexData::fromRequest($request));

        return Inertia::render('orders/index', [
            ...$result->toArray(),
        ]);
    }

    public function show(Request $request, int $order, ShowOrder $action): Response
    {
        $result = $action->handle(OrderShowData::fromRequest($request, $order));

        return Inertia::render('orders/show', [
            'order' => $result->toArray(),
        ]);
    }
}
