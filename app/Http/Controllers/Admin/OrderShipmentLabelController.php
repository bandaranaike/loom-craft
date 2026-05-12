<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\ShipmentLabelDataBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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

    public function download(Order $order, Shipment $shipment): Response
    {
        Gate::authorize('viewAny', Order::class);

        $data = $this->labelDataBuilder->build($order, $shipment);

        return Pdf::loadView('fulfillment.shipment-label', [
            'label' => $data,
            'printMode' => 'pdf',
        ])
            ->setPaper([0, 0, 288, 432])
            ->setWarnings(false)
            ->download($this->filename($data['shipment_number']));
    }

    private function filename(string $shipmentNumber): string
    {
        return Str::slug($shipmentNumber) === ''
            ? 'shipment-label.pdf'
            : sprintf('%s-label.pdf', Str::slug($shipmentNumber));
    }
}
