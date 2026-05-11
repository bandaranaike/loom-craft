<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\ShipmentLabelDataBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrderShipmentLabelController extends Controller
{
    public function __construct(
        private readonly ShipmentLabelDataBuilder $labelDataBuilder,
    ) {}

    public function __invoke(Order $order, Shipment $shipment): Response
    {
        Gate::authorize('viewAny', Order::class);

        $data = $this->labelDataBuilder->build($order, $shipment);

        return response()
            ->view('fulfillment.shipment-label', [
                'label' => $data,
                'printMode' => 'web',
            ])
            ->header('Content-Disposition', sprintf('inline; filename="%s.html"', $data['shipment_number']))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
