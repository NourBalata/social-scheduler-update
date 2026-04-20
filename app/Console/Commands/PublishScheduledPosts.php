<?php

namespace App\Console\Commands;

use App\Models\ScheduledPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish';
    protected $description = 'publish posts!!';

    public function handle()
    {
        $this->info('Starting check for scheduled posts...');
        $posts = ScheduledPost::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with(['facebookPage'])
            ->get();

        $this->info("Posts ready to publish: {$posts->count()}");

        foreach ($posts as $post) {
            $this->publishPost($post);
        }
    }

    private function publishPost(ScheduledPost $post)
{
    try {
        $page = $post->facebookPage;
        $accessToken = $page->access_token;
        $url = "https://graph.facebook.com/v18.0";


        if (!empty($post->media) && is_array($post->media)) {
            
         
            $mediaItem = $post->media[0]; 
            $filePath = storage_path('app/public/' . ltrim($mediaItem['path'], '/'));

            $this->info("Checking file at: " . $filePath);

            if (file_exists($filePath)) {
                $fileContents = file_get_contents($filePath);
                $fileName = basename($filePath);

       
                $endpoint = ($mediaItem['type'] === 'video') ? 'videos' : 'photos';
                $captionField = ($mediaItem['type'] === 'video') ? 'description' : 'caption';

                $response = Http::timeout(120)
                    ->attach('source', $fileContents, $fileName)
                    ->post("{$url}/{$page->page_id}/{$endpoint}", [
                        'access_token' => $accessToken,
                        $captionField => $post->content,
                    ]);
            } else {
                $this->error("File not found on disk: " . $filePath);
                return;
            }
        } else {
        
            $response = Http::timeout(60)->post("{$url}/{$page->page_id}/feed", [
                'access_token' => $accessToken,
                'message' => $post->content,
            ]);
        }

        if ($response->successful()) {
            $post->update(['status' => 'published', 'published_at' => now()]);
            $this->info("Published successfully!");
        } else {
            $this->error("FB Error: " . $response->body());
        }

    } catch (\Exception $e) {
        $this->error("Error: " . $e->getMessage());
    }
}
}