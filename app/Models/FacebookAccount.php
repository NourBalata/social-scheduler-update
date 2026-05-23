<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookAccount extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_id',
        'name',
        'email',
        'avatar',
        'access_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'access_token' => 'encrypted', 
    ];

  
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function facebookPages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }

 
    public function hasValidToken(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isFuture();
    }
}