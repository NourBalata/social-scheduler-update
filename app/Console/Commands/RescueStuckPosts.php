<?php

namespace App\Console\Commands;

use App\Models\ScheduledPost;
use Illuminate\Console\Command;

class RescueStuckPosts extends Command
{
    protected $signature   = 'posts:rescue';
    protected $description = 'Reset posts stuck in "processing" status back to "pending" (runs if a worker died mid-job).';

    public function handle(): int
    {
        $stuck = ScheduledPost::stuck()->get();

        if ($stuck->isEmpty()) {
            $this->info('No stuck posts found.');
            return self::SUCCESS;
        }

        $this->warn("Found {$stuck->count()} stuck post(s). Resetting to pending...");

        foreach ($stuck as $post) {
            $post->update(['status' => 'pending']);
            $this->line("  ↩ Reset post #{$post->id} (stuck since {$post->updated_at})");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}