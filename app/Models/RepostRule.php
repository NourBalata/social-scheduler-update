<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepostRule extends Model
{
    protected $fillable = [
        'original_post_id',
        'user_id',
        'facebook_page_id',
        'interval',
        'original_content',
        'last_reposted_at',
        'next_repost_at',
        'is_active',
        'repost_count',
    ];

    protected $casts = [
        'last_reposted_at' => 'datetime',
        'next_repost_at'   => 'datetime',
        'is_active'        => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function originalPost(): BelongsTo
    {
        return $this->belongsTo(ScheduledPost::class, 'original_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * احسب الـ next_repost_at بناءً على الـ interval
     */
    public function calculateNextRepostAt(): Carbon
    {
        $base = $this->last_reposted_at ?? now();

        return match ($this->interval) {
            'weekly'  => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonth(),
        };
    }

    /**
     * بعد كل نشر، حدّث الـ timestamps والعداد
     */
    public function markAsReposted(): void
    {
        $this->update([
            'last_reposted_at' => now(),
            'next_repost_at'   => $this->calculateNextRepostAt(),
            'repost_count'     => $this->repost_count + 1,
        ]);
    }

    /**
     * scope للقواعد الجاهزة للنشر الآن
     */
    public function scopeDue($query)
    {
        return $query
            ->where('is_active', true)
            ->where('next_repost_at', '<=', now());
    }
}