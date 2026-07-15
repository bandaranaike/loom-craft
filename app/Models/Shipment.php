<?php

namespace App\Models;

use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shipment_number',
        'order_id',
        'vendor_id',
        'responsibility',
        'status',
        'shipping_carrier_id',
        'shipping_service_id',
        'carrier',
        'service_level',
        'tracking_number',
        'vendor_preparing_at',
        'vendor_handed_to_admin_at',
        'admin_received_at',
        'quality_checked_at',
        'packed_at',
        'package_count',
        'parcel_item_count',
        'parcel_styles',
        'parcel_materials',
        'parcel_weight',
        'weight_unit',
        'parcel_length',
        'parcel_width',
        'parcel_height',
        'parcel_dimension_unit',
        'shipped_at',
        'delivered_at',
        'delivery_recipient_name',
        'delivery_proof_reference',
        'delivery_evidence_path',
        'delivery_evidence_original_name',
        'delivery_evidence_mime_type',
        'delivery_evidence_uploaded_at',
        'delivery_confirmed_by',
        'delivery_note',
        'delivery_exception_reason',
        'delivery_exception_note',
        'delivery_exception_at',
        'failed_delivery_attempts',
    ];

    protected static function booted(): void
    {
        static::created(function (self $shipment): void {
            if (! is_string($shipment->shipment_number) || $shipment->shipment_number === '') {
                $shipment->forceFill([
                    'shipment_number' => self::newShipmentNumber($shipment),
                ])->saveQuietly();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parcel_weight' => 'decimal:2',
            'parcel_item_count' => 'integer',
            'parcel_length' => 'decimal:2',
            'parcel_width' => 'decimal:2',
            'parcel_height' => 'decimal:2',
            'vendor_preparing_at' => 'datetime',
            'vendor_handed_to_admin_at' => 'datetime',
            'admin_received_at' => 'datetime',
            'quality_checked_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'delivery_evidence_uploaded_at' => 'datetime',
            'delivery_exception_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function shippingCarrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class);
    }

    public function shippingService(): BelongsTo
    {
        return $this->belongsTo(ShippingService::class);
    }

    public function deliveryConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_confirmed_by');
    }

    public function fulfillmentStatusHistories(): HasMany
    {
        return $this->hasMany(FulfillmentStatusHistory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    private static function newShipmentNumber(self $shipment): string
    {
        $date = $shipment->created_at instanceof Carbon
            ? $shipment->created_at
            : Carbon::parse($shipment->created_at ?? now());

        return sprintf('SHP-%s-%06d', $date->format('Ym'), $shipment->id);
    }
}
