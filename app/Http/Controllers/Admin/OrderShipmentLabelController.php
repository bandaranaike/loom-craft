<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\ShipmentLabelDataBuilder;
use App\Services\Fulfillment\ShipmentLabelPdfGenerator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class OrderShipmentLabelController extends Controller
{
    public function __construct(
        private readonly ShipmentLabelDataBuilder $labelDataBuilder,
        private readonly ShipmentLabelPdfGenerator $labelPdfGenerator,
    ) {}

    public function __invoke(Order $order, Shipment $shipment): Response
    {
        Gate::authorize('viewAny', Order::class);

        $data = $this->labelDataBuilder->build($order, $shipment);

        return response()
            ->view('fulfillment.shipment-label', [
                'label' => $data,
                'printMode' => 'web',
                'downloadUrl' => route('admin.orders.shipments.label.download', [
                    'order' => $order,
                    'shipment' => $shipment,
                ]),
            ])
            ->header('Content-Disposition', sprintf('inline; filename="%s.html"', $data['shipment_number']))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function download(Order $order, Shipment $shipment): Response
    {
        Gate::authorize('viewAny', Order::class);

        $data = $this->labelDataBuilder->build($order, $shipment);

        return $this->downloadResponse($this->labelPdfGenerator->generate($data), $this->filename($data['shipment_number']));
    }

    private function downloadResponse(string $pdf, string $filename): Response
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename=%s', $filename),
        ]);
    }

    private function filename(string $shipmentNumber): string
    {
        return Str::slug($shipmentNumber) === ''
            ? 'shipment-label.pdf'
            : sprintf('%s-label.pdf', Str::slug($shipmentNumber));
    }
}
