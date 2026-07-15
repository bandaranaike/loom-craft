<?php

namespace App\Services\Fulfillment;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Support\Site;
use Illuminate\Support\Collection;

class ShipmentLabelDataBuilder
{
    public function __construct(
        private readonly ShipmentLabelCodeGenerator $codeGenerator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Order $order, Shipment $shipment): array
    {
        if ($shipment->order_id !== $order->id) {
            abort(404);
        }

        $order->loadMissing(['addresses', 'invoice', 'items.product', 'items.productVariation', 'items.vendor', 'user']);
        $shipment->loadMissing(['vendor', 'items.orderItem.product', 'items.orderItem.productVariation', 'items.orderItem.vendor']);

        $shippingAddress = $order->addresses->firstWhere('type', 'shipping');
        $parcelItems = $this->parcelItems($shipment, $order->items);
        $primaryItem = $parcelItems->first()['item'] ?? $order->items->first();
        $site = Site::current();

        $orderNumber = $order->order_number ?? $order->public_id ?? sprintf('Order #%d', $order->id);
        $invoiceNumber = $order->invoice?->invoice_number ?? 'Pending';
        $shipmentNumber = $shipment->shipment_number ?? sprintf('Shipment #%d', $shipment->id);
        $trackingNumber = $shipment->tracking_number ?? 'Pending';
        $trackingPayload = $order->public_id === null
            ? sprintf('%s | %s', $shipmentNumber, $trackingNumber)
            : route('orders.show', ['order' => $order->public_id]);

        return [
            'document_title' => sprintf('Shipment Label %s', $shipment->shipment_number ?? $shipment->id),
            'brand_name' => (string) ($site['display_name'] ?? $site['name'] ?? 'LoomCraft'),
            'order_number' => $orderNumber,
            'public_id' => $order->public_id,
            'invoice_number' => $invoiceNumber,
            'shipment_number' => $shipmentNumber,
            'tracking_number' => $trackingNumber,
            'carrier' => $shipment->carrier ?? 'Pending',
            'service_level' => $shipment->service_level ?? 'Standard',
            'order_date' => $order->placed_at?->format('d M Y') ?? $order->created_at?->format('d M Y'),
            'ship_to' => $this->shippingAddress($order, $shippingAddress),
            'return_to' => $this->returnAddress($site),
            'parcel' => $this->parcel($shipment, $parcelItems),
            'product' => $this->primaryProduct($primaryItem, $parcelItems->count()),
            'products' => $parcelItems->map(fn (array $parcelItem): array => [
                'name' => $parcelItem['item']->product?->name ?? 'Product',
                'code' => $parcelItem['item']->product?->product_code,
                'quantity' => $parcelItem['quantity'],
                'vendor' => $parcelItem['item']->vendor?->display_name ?? $parcelItem['item']->product?->vendor?->display_name,
            ])->values()->all(),
            'codes' => [
                'tracking_payload' => $trackingPayload,
                'tracking_barcode' => $this->codeGenerator->barcodeDataUri($trackingNumber, 2.2, 58),
                'order_barcode' => $this->codeGenerator->barcodeDataUri($orderNumber, 1.2, 36),
                'invoice_barcode' => $this->codeGenerator->barcodeDataUri($invoiceNumber, 1.2, 36),
                'tracking_qr' => $this->codeGenerator->qrDataUri($trackingPayload),
            ],
            'assets' => $this->assets(),
            'print_generated_at' => now()->format('d M Y H:i'),
        ];
    }

    /**
     * @return array{name: string, lines: list<string>, phone: ?string}
     */
    private function shippingAddress(Order $order, ?OrderAddress $address): array
    {
        return [
            'name' => $address?->full_name ?? $order->user?->name ?? $order->guest_name ?? 'Customer',
            'lines' => array_values(array_filter([
                $address?->line1,
                $address?->line2,
                trim(implode(', ', array_filter([$address?->city, $address?->region]))),
                trim(implode(' ', array_filter([$address?->postal_code, $address?->country_code]))),
            ], static fn (?string $line): bool => is_string($line) && $line !== '')),
            'phone' => $address?->phone,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parcel(Shipment $shipment, Collection $parcelItems): array
    {
        $weight = $shipment->parcel_weight === null
            ? 'Pending'
            : sprintf('%s %s', $shipment->parcel_weight, $shipment->weight_unit ?? 'kg');

        $dimensions = collect([$shipment->parcel_length, $shipment->parcel_width, $shipment->parcel_height])
            ->filter(fn (mixed $value): bool => $value !== null)
            ->map(fn (mixed $value): string => (string) $value)
            ->implode(' x ');

        return [
            'package_count' => (string) ($shipment->package_count ?? 1),
            'item_count' => $shipment->parcel_item_count ?? $parcelItems->sum('quantity'),
            'styles' => $this->parcelOverrideOrDerived($shipment->parcel_styles, $parcelItems
                ->map(fn (array $parcelItem): ?string => $parcelItem['item']->product?->name)
                ->filter()
                ->unique()
                ->implode('; ')),
            'materials' => $this->parcelOverrideOrDerived($shipment->parcel_materials, $parcelItems
                ->map(fn (array $parcelItem): ?string => $parcelItem['item']->product?->materials)
                ->filter()
                ->unique()
                ->implode('; ')),
            'sizes' => $this->parcelSizes($parcelItems),
            'weight' => $weight,
            'dimensions' => $dimensions === '' ? 'Pending' : sprintf('%s %s', $dimensions, $shipment->parcel_dimension_unit ?? 'cm'),
        ];
    }

    /**
     * @param  Collection<int, OrderItem>  $orderItems
     * @return Collection<int, array{item: OrderItem, quantity: int}>
     */
    private function parcelItems(Shipment $shipment, Collection $orderItems): Collection
    {
        if ($shipment->items->isEmpty()) {
            return $orderItems->map(fn (OrderItem $item): array => [
                'item' => $item,
                'quantity' => (int) $item->quantity,
            ]);
        }

        return $shipment->items
            ->filter(fn (ShipmentItem $shipmentItem): bool => $shipmentItem->orderItem instanceof OrderItem)
            ->map(fn (ShipmentItem $shipmentItem): array => [
                'item' => $shipmentItem->orderItem,
                'quantity' => (int) $shipmentItem->quantity,
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array{name: string, lines: list<string>, phone: ?string}
     */
    private function returnAddress(array $site): array
    {
        $address = $site['fulfillment_return_address'] ?? [];

        return [
            'name' => (string) ($address['name'] ?? 'LoomCraft Fulfillment Center'),
            'lines' => array_values(array_map('strval', $address['lines'] ?? ['Colombo 05', 'Sri Lanka'])),
            'phone' => isset($address['phone']) ? (string) $address['phone'] : null,
        ];
    }

    private function parcelOverrideOrDerived(?string $override, string $derived): string
    {
        return filled($override) ? $override : ($derived !== '' ? $derived : 'Pending');
    }

    /**
     * @param  Collection<int, array{item: OrderItem, quantity: int}>  $parcelItems
     */
    private function parcelSizes(Collection $parcelItems): string
    {
        return $this->parcelOverrideOrDerived(null, $parcelItems
            ->map(function (array $parcelItem): ?string {
                $item = $parcelItem['item'];
                $size = $item->product_variation_label ?? $item->productVariation?->label;

                if (! is_string($size) || trim($size) === '') {
                    return null;
                }

                return sprintf('%s: %s', $item->product?->name ?? 'Product', trim($size));
            })
            ->filter()
            ->unique()
            ->implode('; '));
    }

    /**
     * @return array<string, int|string|null>
     */
    private function primaryProduct(?OrderItem $item, int $itemCount): array
    {
        $product = $item?->product;
        $variation = $item?->productVariation;

        $dimensions = collect([$variation?->dimension_length, $variation?->dimension_width, $variation?->dimension_height])
            ->filter(fn (mixed $value): bool => $value !== null)
            ->map(fn (mixed $value): string => (string) $value)
            ->implode(' x ');

        return [
            'name' => $product?->name ?? 'LoomCraft Product',
            'code' => $product?->product_code,
            'quantity' => $item?->quantity ?? 0,
            'item_count' => $itemCount,
            'dimensions' => $dimensions === '' ? null : sprintf('%s %s', $dimensions, $product?->dimension_unit ?? 'cm'),
            'vendor' => $item?->vendor?->display_name,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function assets(): array
    {
        $guidelinesPath = base_path('.ai/knowledge/assets/guidelines');

        return [
            'logo' => asset((string) (Site::current()['label_logo'] ?? 'brand/loomcraft-logo.png')),
            'fragile' => $this->codeGenerator->imageDataUri($guidelinesPath.'/fragile.png'),
            'hand_made' => $this->codeGenerator->imageDataUri($guidelinesPath.'/hand-made.png'),
            'handle_with_care' => $this->codeGenerator->imageDataUri($guidelinesPath.'/handle-with-care.png'),
            'keep_dry' => $this->codeGenerator->imageDataUri($guidelinesPath.'/keep-dry.png'),
            'recycle' => $this->codeGenerator->imageDataUri($guidelinesPath.'/recycle.png'),
            'made_in_sri_lanka' => $this->codeGenerator->imageDataUri($guidelinesPath.'/made-in-sri-lanka.png'),
        ];
    }
}
