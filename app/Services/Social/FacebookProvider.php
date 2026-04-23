<?php
namespace App\Services\Social;

use App\Contracts\SocialMediaProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookProvider implements SocialMediaProvider
{
    private string $base = 'https://graph.facebook.com/v19.0';

   public function getAuthUrl(?string $clientId = null): string
    {
        return 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
            'client_id'    => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect_uri'),
            'scope'        => 'pages_manage_posts,pages_read_engagement,pages_show_list',
            'state'        => csrf_token(),
        ]);
    }

    public function getAccessToken(string $code): array
    {
        $res = Http::get("{$this->base}/oauth/access_token", [
            'client_id'     => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri'  => config('services.facebook.redirect_uri'),
            'code'          => $code,
        ]);
        return $res->json() ?? [];
    }

    public function getUserPages(string $userToken): array
    {
        $res = Http::get("{$this->base}/me/accounts", [
            'access_token' => $userToken,
            'fields'       => 'id,name,access_token,category',
        ]);
        return $res->json('data') ?? [];
    }

    public function post(string $token, string $pageId, array $data): string
    {
        $endpoint = "{$this->base}/{$pageId}/feed";
        $payload  = [
            'message'      => $data['content'] ?? $data['message'] ?? '',
            'access_token' => $token,
        ];

        if (!empty($data['media_url'])) {
            $type = $data['media_type'] ?? 'image';
            if ($type === 'image') {
                $endpoint       = "{$this->base}/{$pageId}/photos";
                $payload['url'] = $data['media_url'];
            } elseif ($type === 'video') {
                $endpoint            = "{$this->base}/{$pageId}/videos";
                $payload['file_url'] = $data['media_url'];
            }
        }

        $response = Http::timeout(30)->post($endpoint, $payload);

        if ($response->failed()) {
            $msg = $response->json('error.message') ?? $response->body();
            Log::error('fb post failed', ['page' => $pageId, 'error' => $msg]);
            throw new \Exception("Facebook API Error: " . $msg);
        }

        return (string) ($response->json('id') ?? $response->json('post_id') ?? '');
    }

}