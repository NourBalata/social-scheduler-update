<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ScheduledPost extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_page_id',
        'content',
        'media',
        'scheduled_at',
        'status',
        'facebook_post_id',
        'error_message',
        'published_at',
    ];

    protected $casts = [
        'media' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'status' => 'string', // عشان نضمن المعاملة كـ string دايماً
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // --- Actions ---

    public function markAsPublished(string $fbPostId): void
    {
        $this->update([
            'status' => 'published',
            'facebook_post_id' => $fbPostId,
            'published_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => substr($error, 0, 1000), // أمان: عشان ما يضرب الـ String limit لو الخطأ طويل
        ]);
    }

    // --- Scopes ---

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->pending()
            ->where('scheduled_at', '<=', now());
    }
}