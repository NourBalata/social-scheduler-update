<?php

namespace App\Services\Social;

use App\Contracts\SocialMediaProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookProvider implements SocialMediaProvider
{
    private string $graphUrl  = 'https://graph.facebook.com/v20.0';
    private string $dialogUrl = 'https://www.facebook.com/v20.0/dialog/oauth';

    public function getAuthUrl(): string
    {
        return $this->dialogUrl . '?' . http_build_query([
            'client_id'    => config('services.facebook.client_id'),
            'redirect_uri' => config('services.facebook.redirect_uri'),
            'scope'        => 'pages_manage_posts,pages_read_engagement,pages_show_list',
            'state'        => csrf_token(),
        ]);
    }

    public function getAccessToken(string $code): array
    {
        $response = Http::get("{$this->graphUrl}/oauth/access_token", [
            'client_id'     => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri'  => config('services.facebook.redirect_uri'),
            'code'          => $code,
        ]);

        if ($response->failed()) {
            Log::error('Facebook: failed to obtain access token.', $response->json());
            throw new \Exception('Failed to obtain Facebook access token.');
        }

        return $response->json();
    }

    public function getLongLivedToken(string $shortToken): array
    {
        $response = Http::get("{$this->graphUrl}/oauth/access_token", [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.facebook.client_id'),
            'client_secret'     => config('services.facebook.client_secret'),
            'fb_exchange_token' => $shortToken,
        ]);

        if ($response->failed()) {
            Log::error('Facebook: failed to exchange for long-lived token.', $response->json());
            throw new \Exception('Failed to obtain long-lived Facebook token.');
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'expires_in'   => $data['expires_in'] ?? 5184000,
            'expires_at'   => now()->addSeconds($data['expires_in'] ?? 5184000),
        ];
    }

    public function getUserPages(string $userToken): array
    {
        $response = Http::get("{$this->graphUrl}/me/accounts", [
            'access_token' => $userToken,
            'fields'       => 'id,name,access_token,category',
        ]);

        if ($response->failed()) {
            Log::error('Facebook: failed to fetch user pages.', $response->json());
            return [];
        }

        return $response->json('data') ?? [];
    }

    public function post(string $token, string $pageId, array $data): string
    {
        $endpoint = "{$this->graphUrl}/{$pageId}/feed";
        $payload  = [
            'message'      => $data['content'] ?? $data['message'] ?? '',
            'access_token' => $token,
        ];

        if (! empty($data['media_url'])) {
            $type = $data['media_type'] ?? 'image';

            if ($type === 'image') {
                $endpoint       = "{$this->graphUrl}/{$pageId}/photos";
                $payload['url'] = $data['media_url'];
            } elseif ($type === 'video') {
                $endpoint            = "{$this->graphUrl}/{$pageId}/videos";
                $payload['file_url'] = $data['media_url'];
            }
        }

        $response = Http::timeout(30)->post($endpoint, $payload);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            Log::error('Facebook: post failed.', ['page_id' => $pageId, 'error' => $message]);
            throw new \Exception("Facebook API Error: {$message}");
        }

        return (string) ($response->json('id') ?? $response->json('post_id') ?? '');
    }

    public function validateToken(string $token): bool
    {
        $response = Http::get("{$this->graphUrl}/me", [
            'access_token' => $token,
        ]);

        return $response->successful();
    }

    public function syncAccount(User $user, string $code): int
    {
        return DB::transaction(function () use ($user, $code) {
            $tokenData = $this->getAccessToken($code);
            $longLived = $this->getLongLivedToken($tokenData['access_token']);

            $account = $user->facebookAccounts()->updateOrCreate(
                ['facebook_id' => $tokenData['user_id'] ?? $user->id],
                [
                    'name'             => $tokenData['name'] ?? 'Facebook User',
                    'access_token'     => encrypt($longLived['access_token']),
                    'token_expires_at' => $longLived['expires_at'],
                ]
            );

            $pages = $this->getUserPages($longLived['access_token']);

            foreach ($pages as $page) {
                $user->facebookPages()->updateOrCreate(
                    ['page_id' => (string) $page['id']],
                    [
                        'page_name'           => $page['name'],
                        'facebook_account_id' => $account->id,
                        'access_token'        => encrypt($page['access_token']),
                        'is_active'           => true,
                    ]
                );
            }

            return count($pages);
        });
    }
}
