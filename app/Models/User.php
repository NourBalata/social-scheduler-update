<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'plan_id', 'is_admin', 'plan_expires_at',
        'fb_client_id', 'fb_client_secret', 'fb_user_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_expires_at'   => 'datetime',
        'password'          => 'hashed',
        'is_admin'          => 'boolean',
    ];

    // --- Relations ---

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function facebookAccounts(): HasMany
    {
        return $this->hasMany(FacebookAccount::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    // --- SaaS Logic ---

    public function hasActivePlan(): bool
    {
        if (!$this->plan_id) return false;
        
        if ($this->plan_expires_at && $this->plan_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function canSchedulePost(): bool
    {
        // تم التعديل لاستخدام currentPlan بدلاً من plan
        if (!$this->hasActivePlan() || !$this->currentPlan) {
            return false;
        }

        $usedThisMonth = $this->posts()
            ->whereYear('scheduled_at', now()->year)
            ->whereMonth('scheduled_at', now()->month)
            ->count();

        return $usedThisMonth < $this->currentPlan->posts_limit;
    }

    public function remainingPostsCount(): int
    {
        if (!$this->hasActivePlan() || !$this->currentPlan) return 0;

        $used = $this->posts()
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->count();

        return max(0, $this->currentPlan->posts_limit - $used);
    }

    public function canAddPage(): bool
    {
        // تم التعديل هنا أيضاً لاستخدام currentPlan
        if (!$this->hasActivePlan() || !$this->currentPlan) return false;

        return $this->pages()->count() < $this->currentPlan->pages_limit;
    }
}