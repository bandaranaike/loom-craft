<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\ShipmentLabelDataBuilder;
use App\Services\Fulfillment\ShipmentLabelPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class OrderShipmentLabelController extends Controller
{
    public function __construct(
        private readonly ShipmentLabelDataBuilder $labelDataBuilder,
        private readonly ShipmentLabelPdfGenerator $labelPdfGenerator,
    ) {}

    public function __invoke(Request $request, Order $order, Shipment $shipment): Response
    {
        $this->authorizeLabelAccess($request, $order);

        $data = $this->labelDataBuilder->build($order, $shipment);

        return response()
            ->view('fulfillment.shipment-label', [
                'label' => $data,
                'printMode' => 'mobile',
                'downloadUrl' => url("api/v1/admin/orders/{$order->id}/shipments/{$shipment->id}/label.pdf"),
            ])
            ->header('Content-Disposition', sprintf('inline; filename="%s.html"', $data['shipment_number']))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function download(Request $request, Order $order, Shipment $shipment): Response
    {
        $this->authorizeLabelAccess($request, $order);

        $data = $this->labelDataBuilder->build($order, $shipment);

        return $this->downloadResponse($this->labelPdfGenerator->generate($data), $this->filename($data['shipment_number']));
    }

    private function authorizeLabelAccess(Request $request, Order $order): void
    {
        $user = $request->user();

        if ($user === null || $user->currentAccessToken()?->can('stickers:read') !== true) {
            abort(403);
        }

        if ($user->role !== 'admin' && ! $user->can('viewVendor', $order)) {
            abort(403);
        }
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
