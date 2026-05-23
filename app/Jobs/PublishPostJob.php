<?php

namespace App\Jobs;

use App\Models\ScheduledPost;
use App\Contracts\SocialMediaProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Exception;

class PublishPostJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 120, 300];
    public $timeout = 120;


    public int $uniqueFor = 660; 

    protected $post;

    public function __construct(ScheduledPost $post)
    {
        $this->post = $post;
    }

    public function uniqueId(): string
    {
        return 'publish-post-' . $this->post->id;
    }

    public function handle(SocialMediaProvider $facebookService): void
    {
       
        $claimed = DB::transaction(function () {
            $post = ScheduledPost::lockForUpdate()->find($this->post->id);

            if (!$post || $post->status !== 'pending') {
                return false;
            }

            $post->update(['status' => 'processing']);
            return true;
        });

        if (!$claimed) {
            return; 
        }

        
        $post = $this->post->fresh();

        try {
            $page = $post->facebookPage;

            if (!$page || !$page->isTokenValid()) {
                throw new Exception("Token not valid.");
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
            $post->markAsFailed($e->getMessage());
            throw $e; 
        }
    }

 public function failed(Exception $exception): void
{
    $post = $this->post->fresh();
    if ($post && $post->status === 'processing') {
        $post->markAsFailed('Job failed after all retries: ' . $exception->getMessage());

      
        $post->user->notify(new \App\Notifications\PostFailedNotification($post));
    }

    }
}