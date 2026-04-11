<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookPage extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_account_id',
        'page_id',
        'page_name',
        'access_token',
        'token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'access_token' => 'encrypted', 
    ];

    protected $hidden = [
        'access_token', 
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

  
    public function facebookAccount(): BelongsTo
    {
        return $this->belongsTo(FacebookAccount::class);
    }

   
    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

   
    public function isTokenValid(): bool
    {
        return $this->is_active && ($this->token_expires_at === null || $this->token_expires_at->isFuture());
    }
}