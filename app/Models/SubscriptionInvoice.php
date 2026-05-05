<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'status',
        'amount',
        'currency',
        'period_start',
        'period_end',
        'paid_at',
        'invoice_pdf_url',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'period_start' => 'datetime',
        'period_end'   => 'datetime',
        'paid_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}