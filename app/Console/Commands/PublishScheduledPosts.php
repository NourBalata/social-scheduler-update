<?php

namespace App\Console\Commands;

use App\Jobs\PublishPostJob;
use App\Models\ScheduledPost;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature   = 'posts:publish';
    protected $description = 'Dispatch pending scheduled posts that are due for publishing.';

    public function handle(): int
    {
        $dispatched = 0;

        // chunk(100) بدل ->get() — يمنع تحميل آلاف الـ records في الذاكرة دفعة
        ScheduledPost::ready()
            ->with('facebookPage')
            ->chunkById(100, function ($posts) use (&$dispatched) {
                foreach ($posts as $post) {
                    // jitter عشوائي 0-55 ثانية لتوزيع الـ load على Facebook API
                    // ومنع hitting rate limit لو في كثير منشورات في نفس الدقيقة
                    $jitter = rand(0, 55);
                    PublishPostJob::dispatch($post)->delay(now()->addSeconds($jitter));
                    $dispatched++;
                }
            });

        $dispatched > 0
            ? $this->info("Dispatched {$dispatched} post(s).")
            : $this->info('No posts due for publishing.');

        return self::SUCCESS;
    }
}