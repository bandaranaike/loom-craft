<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Order\ListAdminOrders;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
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
}
