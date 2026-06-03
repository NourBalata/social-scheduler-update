<?php

namespace App\Jobs;

use App\Contracts\SocialMediaProvider;
use App\Models\ScheduledPost;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;


class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public array $backoff = [60, 120, 300];
    public int   $timeout = 120;

    public function __construct(
        protected ScheduledPost $post
    ) {}

    public function handle(SocialMediaProvider $facebookService): void
    {
    
        $claimed = DB::transaction(function () {
            $post = ScheduledPost::lockForUpdate()->find($this->post->id);

            if (! $post || $post->status !== 'pending') {
                return false;
            }

            $post->update(['status' => 'processing']);
            return true;
        });

        if (! $claimed) {
            return;
        }


        $post = ScheduledPost::with('facebookPage')->find($this->post->id);

        try {
            $page = $post->facebookPage;

            if (! $page || ! $page->isTokenValid()) {
                throw new Exception('Facebook page token is missing or expired.');
            }

            $fbPostId = $facebookService->post(
                $page->access_token,
                $page->page_id,
                [
                    'content' => $post->content,
                    'media'   => $post->media,
                ]
            );

            $post->markAsPublished($fbPostId);

        } catch (Exception $e) {

            $post->update(['status' => 'pending', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {

        $post = ScheduledPost::with('user')->find($this->post->id);

        if (! $post) {
            return;
        }

        $post->markAsFailed('Failed after all retries: ' . $exception->getMessage());

        $post->user?->notify(new \App\Notifications\PostFailedNotification($post));
    }
}