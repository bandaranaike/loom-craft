<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'guest_name',
        'guest_email',
        'status',
        'currency',
        'subtotal',
        'commission_total',
        'total',
        'shipping_responsibility',
        'placed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (! is_string($order->public_id) || $order->public_id === '') {
                $order->public_id = self::newPublicId();
            }
        });

        static::created(function (Order $order): void {
            $updates = [];

            if (! is_string($order->order_number) || $order->order_number === '') {
                $updates['order_number'] = self::newOrderNumber($order);
            }

            if ($updates !== []) {
                $order->forceFill($updates)->saveQuietly();
            }

            if (! $order->relationLoaded('invoice') && ! $order->invoice()->exists()) {
                $order->invoice()->create([
                    'status' => 'issued',
                    'currency' => $order->currency,
                    'subtotal' => $order->subtotal,
                    'commission_total' => $order->commission_total,
                    'total' => $order->total,
                    'issued_at' => $order->placed_at ?? now(),
                ]);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    private static function newPublicId(): string
    {
        do {
            $publicId = 'ORD-'.Str::upper(Str::random(28));
        } while (self::query()->where('public_id', $publicId)->exists());

        return $publicId;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function fulfillmentStatusHistories(): HasMany
    {
        return $this->hasMany(FulfillmentStatusHistory::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    private static function newOrderNumber(self $order): string
    {
        $date = $order->placed_at ?? $order->created_at ?? now();

        return sprintf('ORD-%s-%06d', $date->format('Ym'), $order->id);
    }
}
