<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class SallaStore extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'store_name',
        'store_url',
        'store_logo',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active'        => 'boolean',
        'access_token'     => 'encrypted',
        'refresh_token'    => 'encrypted',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    // ─── Relationships ─────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Token Helpers ─────────────────────────────────────────────────────

    public function isTokenValid(): bool
    {
        return $this->is_active
            && ($this->token_expires_at === null || $this->token_expires_at->isFuture());
    }

    /**
     * Refresh the access token using the refresh token.
     * Returns true on success.
     */
    public function refreshAccessToken(): bool
    {
        if (empty($this->refresh_token)) {
            return false;
        }

        $response = Http::asForm()->post('https://accounts.salla.sa/oauth2/token', [
            'client_id'     => config('services.salla.client_id'),
            'client_secret' => config('services.salla.client_secret'),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refresh_token,
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        $this->update([
            'access_token'     => $data['access_token'],
            'refresh_token'    => $data['refresh_token'] ?? $this->refresh_token,
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        return true;
    }

    /**
     * Get a valid access token — refreshes automatically if expired.
     */
    public function getValidToken(): ?string
    {
        if ($this->isTokenValid()) {
            return $this->access_token;
        }

        if ($this->refreshAccessToken()) {
            return $this->fresh()->access_token;
        }

        return null;
    }
}