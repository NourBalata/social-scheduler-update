<?php

namespace App\Services;

use App\Models\FacebookPage;
use Illuminate\Support\Facades\Log;

class FacebookTokenService
{
    private $fbService;

    public function __construct(FacebookService $fbService)
    {
        $this->fbService = $fbService;
    }

    // Exchange short-lived token for long-lived token
    public function getLongLivedToken(string $shortToken, string $appId = null, string $appSecret = null): ?array
    {
        try {
            return $this->fbService->getLongLivedToken($shortToken, $appId, $appSecret);
        } catch (\Exception $e) {
            Log::error('Error getting long-lived token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Get page access token
    public function getPageToken(string $userToken, string $pageId): ?string
    {
        return $this->fbService->getPageToken($userToken, $pageId);
    }

    // Refresh token before expiry
    public function refreshTokenIfNeeded(FacebookPage $page): bool
    {
        // Check if token will expire in next 7 days or already expired
        if (!$page->token_expires_at || $page->token_expires_at->lessThanOrEqualTo(now()->addDays(7))) {
            
            Log::info("Refreshing token for page: {$page->page_name}");

            // Get user's app credentials
            $appId = $page->user->fb_client_id ?? config('services.facebook.client_id');
            $appSecret = $page->user->fb_client_secret ?? config('services.facebook.client_secret');

            $newToken = $this->fbService->refreshToken($page->access_token, $appId, $appSecret);

            if ($newToken) {
                $page->update([
                    'access_token' => $newToken['access_token'],
                    'token_expires_at' => $newToken['expires_at'],
                ]);

                Log::info("Token refreshed successfully for page: {$page->page_name}");
                return true;
            }

            Log::error("Failed to refresh token for page: {$page->page_name}");
            return false;
        }

        return true; // Token still valid
    }

    // Validate token
    public function validateToken(string $token): bool
    {
        return $this->fbService->validateToken($token);
    }

    // Debug token info
    public function debugToken(string $token): ?array
    {
        return $this->fbService->debugToken($token);
    }
}