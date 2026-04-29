<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchPostAnalyticsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
   public function handle(): void
{
    $posts = ScheduledPost::where('status', 'published')
        ->whereNotNull('fb_post_id')
        ->get();

    foreach ($posts as $post) {
        $insights = app(FacebookService::class)
            ->getPostInsights($post->facebookPage->access_token, $post->fb_post_id);

        PostAnalytic::updateOrCreate(
            ['scheduled_post_id' => $post->id],
            [
                'likes'       => data_get($insights, '0.values.0.value', 0),
                'reach'       => data_get($insights, '1.values.0.value', 0),
                'impressions' => data_get($insights, '2.values.0.value', 0),
                'fetched_at'  => now(),
            ]
        );
    }
}
}
