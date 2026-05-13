<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\VendorPayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class VendorPayout extends Model
{
    /** @use HasFactory<VendorPayoutFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $payout): void {
            if ($payout->status !== 'paid' || (! $payout->isDirty('status') && ! $payout->isDirty('paid_at'))) {
                return;
            }

            if (! $payout->isEligibleForPayment()) {
                throw new InvalidArgumentException('COD vendor payouts cannot be paid until COD remittance is settled.');
            }
        });
    }

    public function isEligibleForPayment(): bool
    {
        if ($this->order_id === null) {
            return true;
        }

        $payment = Payment::query()
            ->where('order_id', $this->order_id)
            ->first();

        if ($payment === null || $payment->method !== 'cod') {
            return true;
        }

        return $payment->status === PaymentStatus::Paid->value
            && $payment->cod_settled_at !== null;
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
