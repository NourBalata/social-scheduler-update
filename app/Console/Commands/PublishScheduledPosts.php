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
        $posts = ScheduledPost::ready()
            ->with('facebookPage')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts due for publishing.');
            return self::SUCCESS;
        }

        $this->info("Dispatching {$posts->count()} post(s)...");

        foreach ($posts as $post) {
            PublishPostJob::dispatch($post);
            $this->line("  → Dispatched post #{$post->id}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}