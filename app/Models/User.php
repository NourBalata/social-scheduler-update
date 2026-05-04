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

    // ─── Plan Helpers ─────────────────────────────────────────────────────────

    public function hasActivePlan(): bool
    {
        if (! $this->plan_id) {
            return false;
        }

        if ($this->plan_expires_at?->isPast()) {
            return false;
        }

        return true;
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

    public function contentPlans()
{
    return $this->hasMany(ContentPlan::class);
}
}