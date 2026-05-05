<?php

namespace App\Jobs;

use App\Models\ScheduledPost;
use App\Services\FacebookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchPostAnalyticsJob implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        $posts = ScheduledPost::where('status', 'published')
            ->whereNotNull('fb_post_id')
            ->with('facebookPage')
            ->get();

        foreach ($posts as $post) {
            try {
                if (! $post->facebookPage) {
                    Log::warning("FetchPostAnalytics: no page for post {$post->id}");
                    continue;
                }

                // فك تشفير التوكن
                $token = $post->facebookPage->access_token;
                try {
                    $token = decrypt($token);
                } catch (\Exception $e) {
                    // التوكن مش مشفر، استخدمه مباشرة
                }

                $insights = app(FacebookService::class)
                    ->getPostInsights($token, $post->fb_post_id);

                // سجّل النتيجة في اللوج بدل PostAnalytic غير الموجود
                Log::info("Post analytics fetched", [
                    'post_id'     => $post->id,
                    'fb_post_id'  => $post->fb_post_id,
                    'likes'       => data_get($insights, '0.values.0.value', 0),
                    'reach'       => data_get($insights, '1.values.0.value', 0),
                    'impressions' => data_get($insights, '2.values.0.value', 0),
                ]);

            } catch (\Exception $e) {
                Log::error("FetchPostAnalytics failed for post {$post->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}