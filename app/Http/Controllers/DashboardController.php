<?php

namespace App\Http\Controllers;

use App\Actions\Order\ListDashboardOrderHistories;
use App\DTOs\Order\OrderIndexData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ListDashboardOrderHistories $action): Response
    {
        $result = $action->handle(OrderIndexData::fromRequest($request));

        return Inertia::render('dashboard', [
            'status' => session('status'),
            ...$result->toArray(),
        ]);
    }
}
