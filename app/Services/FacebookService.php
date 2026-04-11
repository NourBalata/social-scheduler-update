<?php

namespace App\Services;

use App\Contracts\SocialMediaProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService implements SocialMediaProvider
{
    protected string $baseUrl = 'https://graph.facebook.com/v18.0';

    public function getAuthUrl(string $clientId = null): string
    {
        $clientId = $clientId ?? config('services.facebook.client_id');
        return "https://www.facebook.com/v20.0/dialog/oauth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => config('services.facebook.redirect_uri'),
            'scope' => 'pages_manage_posts,pages_read_engagement,publish_video,pages_show_list',
            'state' => csrf_token(),
        ]);
    }

    public function getAccessToken(string $code, string $clientId = null, string $clientSecret = null): array
    {
        $clientId = $clientId ?? config('services.facebook.client_id');
        $clientSecret = $clientSecret ?? config('services.facebook.client_secret');

        $response = Http::get("{$this->baseUrl}/oauth/access_token", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => config('services.facebook.redirect_uri'),
            'code' => $code,
        ]);

        return $response->json();
    }

    public function getUserPages(string $userToken): array
    {
        $response = Http::get("{$this->baseUrl}/me/accounts", [
            'access_token' => $userToken,
            'fields' => 'id,name,access_token,category',
        ]);

        return $response->json('data') ?? [];
    }

    public function post(string $token, string $pageId, array $data): string
    {
    
        $endpoint = "{$this->baseUrl}/{$pageId}/feed";
        
        $payload = [
            'message' => $data['content'] ?? '',
            'access_token' => $token,
        ];

        if (!empty($data['media_url'])) {
            if (($data['media_type'] ?? '') === 'image') {
                $endpoint = "{$this->baseUrl}/{$pageId}/photos";
                $payload['url'] = $data['media_url'];
            }
        }

        $response = Http::timeout(30)->post($endpoint, $payload);

        if ($response->failed()) {
            $error = $response->json();
            throw new \Exception("Facebook Error: " . ($error['error']['message'] ?? 'Unknown Error'));
        }

        return (string) ($response->json('id') ?? $response->json('post_id'));
    }

    // دوال إضافية قد يحتاجها الـ Interface
    public function validateToken(string $token): bool { return true; }
}