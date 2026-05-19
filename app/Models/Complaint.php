<?php

namespace App\Models;

use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'complaint_number',
        'user_id',
        'order_id',
        'shipment_id',
        'order_return_id',
        'payment_id',
        'guest_email',
        'category',
        'severity',
        'subject',
        'message',
        'status',
        'resolution_type',
        'resolution_note',
        'courier_claim_reference',
        'handled_by',
        'assigned_to',
        'opened_at',
        'first_response_due_at',
        'sla_due_at',
        'first_responded_at',
        'resolved_at',
        'closed_at',
    ];

    protected static function booted(): void
    {
        static::created(function (self $complaint): void {
            if (! is_string($complaint->complaint_number) || $complaint->complaint_number === '') {
                $complaint->forceFill([
                    'complaint_number' => self::newComplaintNumber($complaint),
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
            'opened_at' => 'datetime',
            'first_response_due_at' => 'datetime',
            'sla_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    private static function newComplaintNumber(self $complaint): string
    {
        $date = $complaint->created_at instanceof Carbon
            ? $complaint->created_at
            : Carbon::parse($complaint->created_at ?? now());

        return sprintf('CMP-%s-%06d', $date->format('Ym'), $complaint->id);
    }
}
