<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'invoice_number',
        'status',
        'currency',
        'subtotal',
        'commission_total',
        'total',
        'issued_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invoice): void {
            if ($invoice->issued_at === null) {
                $invoice->issued_at = $invoice->order?->placed_at ?? now();
            }
        });

        static::created(function (self $invoice): void {
            if (! is_string($invoice->invoice_number) || $invoice->invoice_number === '') {
                $invoice->forceFill([
                    'invoice_number' => self::formatInvoiceNumber($invoice),
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
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    private static function formatInvoiceNumber(self $invoice): string
    {
        $date = $invoice->issued_at instanceof Carbon
            ? $invoice->issued_at
            : Carbon::parse($invoice->issued_at ?? now());

        return sprintf('INV-%s-%06d', $date->format('Ym'), $invoice->id);
    }
}
