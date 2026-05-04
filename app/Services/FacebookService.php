<?php

namespace App\Services;

use App\Contracts\SocialMediaProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacebookService implements SocialMediaProvider
{
    protected string $baseUrl      = 'https://graph.facebook.com/v18.0';
    protected string $videoBaseUrl = 'https://graph-video.facebook.com/v18.0';

    public function getAuthUrl(string $clientId = null): string
    {
        $clientId = $clientId ?? config('services.facebook.client_id');
        return "https://www.facebook.com/v20.0/dialog/oauth?" . http_build_query([
            'client_id'    => $clientId,
            'redirect_uri' => config('services.facebook.redirect_uri'),
            'scope'        => 'pages_manage_posts,pages_read_engagement,publish_video,pages_show_list',
            'state'        => csrf_token(),
        ]);
    }

    public function getAccessToken(string $code, string $clientId = null, string $clientSecret = null): array
    {
        $clientId     = $clientId     ?? config('services.facebook.client_id');
        $clientSecret = $clientSecret ?? config('services.facebook.client_secret');

        $response = Http::get("{$this->baseUrl}/oauth/access_token", [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => config('services.facebook.redirect_uri'),
            'code'          => $code,
        ]);

        if ($response->failed()) {
            Log::error('FB token failed', $response->json());
            throw new \Exception('Error getting token');
        }

        return $response->json();
    }

    public function getLongLivedToken(string $shortToken, string $clientId = null, string $clientSecret = null): array
    {
        $clientId     = $clientId     ?? config('services.facebook.client_id');
        $clientSecret = $clientSecret ?? config('services.facebook.client_secret');

        $response = Http::get("{$this->baseUrl}/oauth/access_token", [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $clientId,
            'client_secret'     => $clientSecret,
            'fb_exchange_token' => $shortToken,
        ]);

        if ($response->failed()) {
            Log::error('Long-lived token failed', $response->json());
            throw new \Exception('Error token');
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
        $response = Http::get("{$this->baseUrl}/me/accounts", [
            'access_token' => $userToken,
            'fields'       => 'id,name,access_token,category',
        ]);

        if ($response->failed()) {
            Log::error('Get pages failed', $response->json());
            return [];
        }

        return $response->json('data') ?? [];
    }

    public function getPageToken(string $userToken, string $pageId): ?string
    {
        $response = Http::get("{$this->baseUrl}/{$pageId}", [
            'fields'       => 'access_token',
            'access_token' => $userToken,
        ]);

        if ($response->failed()) {
            Log::error("Page token failed for {$pageId}", $response->json());
            return null;
        }

        return $response->json('access_token');
    }

    public function post(string $token, string $pageId, array $data): string
    {
        $mediaType = null;
        $mediaPath = null;

   
        if (!empty($data['media'])) {
            if (is_array($data['media']) && isset($data['media'][0])) {
                $mediaType = $data['media'][0]['type'] ?? null;
                $mediaPath = $data['media'][0]['path'] ?? null;
            } elseif (is_array($data['media']) && isset($data['media']['type'])) {
                $mediaType = $data['media']['type'];
                $mediaPath = $data['media']['path'];
            }
        }


        if ($mediaType === 'video' && $mediaPath) {
            $localPath = Storage::disk('public')->path($mediaPath);

            if (!file_exists($localPath)) {
                throw new \Exception("Video file not found: {$localPath}");
            }

            Log::info("Publishing video via resumable upload", [
                'page_id'    => $pageId,
                'local_path' => $localPath,
                'file_size'  => filesize($localPath),
            ]);

            return $this->publishVideoResumable($token, $pageId, $localPath, $data['content'] ?? '');
        }

        
        if ($mediaType === 'image' && $mediaPath) {
            $localPath = Storage::disk('public')->path($mediaPath);

            if (!file_exists($localPath)) {
                throw new \Exception("Image file not found: {$localPath}");
            }

            $response = Http::timeout(60)
                ->attach('source', fopen($localPath, 'r'), basename($localPath))
                ->post("{$this->baseUrl}/{$pageId}/photos", [
                    'message'      => $data['content'] ?? '',
                    'access_token' => $token,
                ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message') ?? 'Unknown Error';
                Log::error('Image post failed', ['error' => $errorMsg, 'page_id' => $pageId]);
                throw new \Exception("Facebook Error: {$errorMsg}");
            }

            return (string) ($response->json('id') ?? $response->json('post_id'));
        }

    
        $response = Http::timeout(30)->post("{$this->baseUrl}/{$pageId}/feed", [
            'message'      => $data['content'] ?? '',
            'access_token' => $token,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? 'Unknown Error';
            Log::error('Text post failed', ['error' => $errorMsg, 'page_id' => $pageId]);
            throw new \Exception("Facebook Error: {$errorMsg}");
        }

        return (string) ($response->json('id') ?? $response->json('post_id'));
    }

  
    protected function publishVideoResumable(string $token, string $pageId, string $localPath, string $description): string
    {
        $fileSize  = filesize($localPath);
        $chunkSize = 5 * 1024 * 1024; 

        $startResponse = Http::timeout(30)->post("{$this->videoBaseUrl}/{$pageId}/videos", [
            'upload_phase'  => 'start',
            'file_size'     => $fileSize,
            'access_token'  => $token,
        ]);

        if ($startResponse->failed()) {
            $errorMsg = $startResponse->json('error.message') ?? 'Start phase failed';
            Log::error('Video upload start failed', ['error' => $errorMsg]);
            throw new \Exception("Facebook Video Start Error: {$errorMsg}");
        }

        $uploadSessionId = $startResponse->json('upload_session_id');
        $startOffset     = (int) $startResponse->json('start_offset');
        $endOffset       = (int) $startResponse->json('end_offset');

        Log::info("Video upload started", [
            'session_id'  => $uploadSessionId,
            'file_size'   => $fileSize,
            'start'       => $startOffset,
            'end'         => $endOffset,
        ]);

      
        $handle = fopen($localPath, 'rb');

        while ($startOffset < $fileSize) {
            fseek($handle, $startOffset);
            $chunkData = fread($handle, $endOffset - $startOffset);

            $transferResponse = Http::timeout(120)
                ->attach('video_file_chunk', $chunkData, 'chunk.mp4')
                ->post("{$this->videoBaseUrl}/{$pageId}/videos", [
                    'upload_phase'      => 'transfer',
                    'upload_session_id' => $uploadSessionId,
                    'start_offset'      => $startOffset,
                    'access_token'      => $token,
                ]);

            if ($transferResponse->failed()) {
                fclose($handle);
                $errorMsg = $transferResponse->json('error.message') ?? 'Transfer phase failed';
                Log::error('Video upload transfer failed', [
                    'error'        => $errorMsg,
                    'start_offset' => $startOffset,
                ]);
                throw new \Exception("Facebook Video Transfer Error: {$errorMsg}");
            }

            $startOffset = (int) $transferResponse->json('start_offset');
            $endOffset   = (int) $transferResponse->json('end_offset');

            Log::info("Video chunk uploaded", [
                'next_start' => $startOffset,
                'next_end'   => $endOffset,
            ]);
        }

        fclose($handle);

       
        $finishResponse = Http::timeout(60)->post("{$this->videoBaseUrl}/{$pageId}/videos", [
            'upload_phase'      => 'finish',
            'upload_session_id' => $uploadSessionId,
            'description'       => $description,
            'access_token'      => $token,
        ]);

        if ($finishResponse->failed()) {
            $errorMsg = $finishResponse->json('error.message') ?? 'Finish phase failed';
            Log::error('Video upload finish failed', ['error' => $errorMsg]);
            throw new \Exception("Facebook Video Finish Error: {$errorMsg}");
        }

        $videoId = (string) ($finishResponse->json('video_id') ?? $finishResponse->json('id') ?? '');

        Log::info("Video published successfully", ['video_id' => $videoId, 'page_id' => $pageId]);

        return $videoId;
    }

    public function validateToken(string $token): bool
    {
        $response = Http::get("{$this->baseUrl}/me", [
            'access_token' => $token,
        ]);

        return $response->successful();
    }

    public function debugToken(string $token): ?array
    {
        $appToken = config('services.facebook.client_id') . '|' . config('services.facebook.client_secret');

        $response = Http::get("{$this->baseUrl}/debug_token", [
            'input_token'  => $token,
            'access_token' => $appToken,
        ]);

        return $response->successful() ? $response->json('data') : null;
    }

 
public function getPageInsights(string $token, string $pageId, string $metric, string $period = 'day'): array
{
    $response = Http::get("{$this->baseUrl}/{$pageId}/insights", [
        'metric'       => $metric,
        'period'       => $period,
        'access_token' => $token,
    ]);
    return $response->json('data') ?? [];
}

public function getPostInsights(string $token, string $postId): array
{
    $response = Http::get("{$this->baseUrl}/{$postId}/insights", [
        'metric'       => 'post_impressions,post_engaged_users,post_reactions_by_type_total',
        'access_token' => $token,
    ]);
    return $response->json('data') ?? [];
}
}