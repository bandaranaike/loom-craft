<?php

namespace App\Models;

use Database\Factories\SuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    /** @use HasFactory<SuggestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'guest_email',
        'title',
        'details',
        'status',
        'handled_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
