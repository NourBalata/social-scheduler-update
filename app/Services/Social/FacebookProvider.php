<?php
namespace App\Services\Social;

use App\Contracts\SocialMediaProvider;
use Illuminate\Support\Facades\Http;

class FacebookProvider implements SocialMediaProvider
{
    // بنمرر البيانات الأساسية وقت إنشاء الكائن
    public function __construct(
        protected string $accessToken,
        protected string $pageId
    ) {}

    public function post(array $data): string
    {
        $response = Http::post("https://graph.facebook.com/v19.0/{$this->pageId}/feed", [
            'message' => $data['message'] ?? '',
            'access_token' => $this->accessToken,
            // لو بدك تضيفي لينك أو ميديا مستقبلاً
            'link' => $data['link'] ?? null,
        ]);

        if ($response->failed()) {
            throw new \Exception("Facebook API Error: " . $response->body());
        }

        return $response->json('id');
    }
}