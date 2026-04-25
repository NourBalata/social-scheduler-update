<?php

namespace App\Jobs;

use App\Models\ScheduledPost;
use App\Contracts\SocialMediaProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  
    public $tries   = 3;
    public $backoff = 60;
    public $timeout = 600;

    protected $post;

    public function __construct(ScheduledPost $post)
    {
        $this->post = $post;
    }

    public function handle(SocialMediaProvider $facebookService): void
    {
        if ($this->post->status !== 'pending') return;

        try {
            $page = $this->post->facebookPage;

            if (!$page || !$page->isTokenValid()) {
                throw new Exception("Token not valid.");
            }

            $media = $this->post->media;

            $fbPostId = $facebookService->post(
                $page->access_token,
                $page->page_id,
                [
                    'content' => $this->post->content,
                    'media'   => $media,
                ]
            );

            $this->post->markAsPublished($fbPostId);

        } catch (Exception $e) {
            $this->post->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}