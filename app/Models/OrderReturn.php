<?php

namespace App\Models;

use Database\Factories\OrderReturnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class OrderReturn extends Model
{
    /** @use HasFactory<OrderReturnFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'return_number',
        'order_id',
        'shipment_id',
        'requested_by',
        'reviewed_by',
        'received_by',
        'shipping_carrier_id',
        'shipping_service_id',
        'status',
        'reason',
        'customer_note',
        'admin_note',
        'resolution',
        'carrier',
        'service_level',
        'tracking_number',
        'package_count',
        'parcel_weight',
        'weight_unit',
        'parcel_length',
        'parcel_width',
        'parcel_height',
        'parcel_dimension_unit',
        'requested_at',
        'approved_at',
        'rejected_at',
        'in_transit_at',
        'admin_received_at',
        'inspected_at',
        'vendor_review_started_at',
        'resolved_at',
        'closed_at',
        'cancelled_at',
    ];

    protected static function booted(): void
    {
        static::created(function (self $orderReturn): void {
            if (! is_string($orderReturn->return_number) || $orderReturn->return_number === '') {
                $orderReturn->forceFill([
                    'return_number' => self::newReturnNumber($orderReturn),
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
            'parcel_length' => 'decimal:2',
            'parcel_width' => 'decimal:2',
            'parcel_height' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'admin_received_at' => 'datetime',
            'inspected_at' => 'datetime',
            'vendor_review_started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function shippingCarrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class);
    }

    public function shippingService(): BelongsTo
    {
        return $this->belongsTo(ShippingService::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function fulfillmentStatusHistories(): HasMany
    {
        return $this->hasMany(FulfillmentStatusHistory::class);
    }

    private static function newReturnNumber(self $orderReturn): string
    {
        $date = $orderReturn->created_at instanceof Carbon
            ? $orderReturn->created_at
            : Carbon::parse($orderReturn->created_at ?? now());

        return sprintf('RET-%s-%06d', $date->format('Ym'), $orderReturn->id);
    }
}
