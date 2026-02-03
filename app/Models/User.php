<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function handledComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'handled_by');
    }

    public function productReports(): HasMany
    {
        return $this->hasMany(ProductReport::class);
    }

    public function handledProductReports(): HasMany
    {
        return $this->hasMany(ProductReport::class, 'handled_by');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class);
    }

    public function handledSuggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class, 'handled_by');
    }

    public function disputesOpened(): HasMany
    {
        return $this->hasMany(Dispute::class, 'opened_by_user_id');
    }

    public function disputesHandled(): HasMany
    {
        return $this->hasMany(Dispute::class, 'handled_by');
    }

    public function paymentsVerified(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function vendorApprovals(): HasMany
    {
        return $this->hasMany(Vendor::class, 'approved_by');
    }
}
