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


    public function getLongLivedToken(string $shortToken, string $appId = null, string $appSecret = null): ?array
    {
        try {
            return $this->fbService->getLongLivedToken($shortToken, $appId, $appSecret);
        } catch (\Exception $e) {
            Log::error('Error getting long-lived token', ['error' => $e->getMessage()]);
            return null;
        }
    }

 
    public function getPageToken(string $userToken, string $pageId): ?string
    {
        return $this->fbService->getPageToken($userToken, $pageId);
    }

   
    public function refreshTokenIfNeeded(FacebookPage $page): bool
    {
        if (!$page->token_expires_at || $page->token_expires_at->lessThanOrEqualTo(now()->addDays(7))) {
            
            Log::info("Refreshing token for: {$page->page_name}");

            $appId = $page->user->fb_client_id ?? config('services.facebook.client_id');
            $appSecret = $page->user->fb_client_secret ?? config('services.facebook.client_secret');

            try {
                $newToken = $this->fbService->getLongLivedToken($page->access_token, $appId, $appSecret);

                if ($newToken) {
                    $page->update([
                        'access_token' => $newToken['access_token'],
                        'token_expires_at' => $newToken['expires_at'],
                    ]);

                    Log::info("Token refreshed: {$page->page_name}");
                    return true;
                }
            } catch (\Exception $e) {
                Log::error("Token refresh failed: {$page->page_name}", ['error' => $e->getMessage()]);
                return false;
            }

            return false;
        }

        return true;
    }


    public function validateToken(string $token): bool
    {
        return $this->fbService->validateToken($token);
    }

   
    public function debugToken(string $token): ?array
    {
        return $this->fbService->debugToken($token);
    }
}