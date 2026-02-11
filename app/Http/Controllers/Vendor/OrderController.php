<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Order\ListVendorOrderItems;
use App\DTOs\Order\OrderIndexData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request, ListVendorOrderItems $action): Response
    {
        $result = $action->handle(OrderIndexData::fromRequest($request));

        return Inertia::render('vendor/orders/index', [
            ...$result->toArray(),
        ]);
    }
}
