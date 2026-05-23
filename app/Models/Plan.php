<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'posts_limit',
        'pages_limit',
        'active',
        'stripe_price_id',   // ← جديد
    ];

    protected $casts = [
        'price'  => 'decimal:2',
        'active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }


    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function hasStripePrice(): bool
    {
        return ! empty($this->stripe_price_id);
    }
}