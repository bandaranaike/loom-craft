<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\ShipmentLabelDataBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderShipmentLabelController extends Controller
{
    public function __construct(
        private readonly ShipmentLabelDataBuilder $labelDataBuilder,
    ) {}

    public function __invoke(Request $request, Order $order, Shipment $shipment): Response
    {
        $user = $request->user();

        if ($user === null || $user->currentAccessToken()?->can('stickers:read') !== true) {
            abort(403);
        }

        if ($user->role !== 'admin' && ! $user->can('viewVendor', $order)) {
            abort(403);
        }

        $data = $this->labelDataBuilder->build($order, $shipment);

        return response()
            ->view('fulfillment.shipment-label', [
                'label' => $data,
                'printMode' => 'mobile',
            ])
            ->header('Content-Disposition', sprintf('inline; filename="%s.html"', $data['shipment_number']))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
