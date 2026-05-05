<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan_id',
        'is_admin',
        'plan_expires_at',
        // Stripe ↓
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'stripe_status',
    ];

    protected $with = ['currentPlan', 'facebookPages'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_expires_at'   => 'datetime',
        'password'          => 'hashed',
        'is_admin'          => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function facebookAccounts(): HasMany
    {
        return $this->hasMany(FacebookAccount::class);
    }

    public function facebookPages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public function contentPlans(): HasMany
    {
        return $this->hasMany(ContentPlan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class)->latest();
    }

    // ─── Plan Helpers ─────────────────────────────────────────────────────────

    public function hasActivePlan(): bool
    {
        if (! $this->plan_id) {
            return false;
        }

        // الخطة المجانية دايماً نشطة
        if ($this->currentPlan?->isFree()) {
            return true;
        }

        // للخطط المدفوعة: نتحقق من Stripe status + انتهاء الفترة
        if ($this->stripe_status === 'active' || $this->stripe_status === 'trialing') {
            return true;
        }

        // fallback على plan_expires_at للخطط اليدوية (admin)
        if ($this->plan_expires_at?->isFuture()) {
            return true;
        }

        return false;
    }

    public function canSchedulePost(): bool
    {
        if (! $this->hasActivePlan() || ! $this->currentPlan) {
            return false;
        }

        return $this->remainingPostsCount() > 0;
    }

    public function remainingPostsCount(): int
    {
        if (! $this->hasActivePlan() || ! $this->currentPlan) {
            return 0;
        }

        $used = $this->scheduledPosts()
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->count();

        return max(0, $this->currentPlan->posts_limit - $used);
    }

    public function canAddPage(): bool
    {
        if (! $this->hasActivePlan() || ! $this->currentPlan) {
            return false;
        }

        return $this->facebookPages()->count() < $this->currentPlan->pages_limit;
    }

    // ─── Stripe Helpers ───────────────────────────────────────────────────────

    public function hasStripeCustomer(): bool
    {
        return ! empty($this->stripe_customer_id);
    }

    public function hasActiveStripeSubscription(): bool
    {
        return ! empty($this->stripe_subscription_id)
            && in_array($this->stripe_status, ['active', 'trialing']);
    }

    public function stripeSubscriptionIsPastDue(): bool
    {
        return $this->stripe_status === 'past_due';
    }
}