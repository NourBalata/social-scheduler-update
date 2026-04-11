<?php

namespace App\Console\Commands;

use App\Models\ScheduledPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish';
    protected $description = 'Publish scheduled posts that are due';

    public function handle()
    {
        $this->info('--- بدء عملية فحص المنشورات ---');
        
        $now = now();
        $this->line("توقيت السيرفر الحالي: {$now}");
        
        // Get pending posts
        $posts = ScheduledPost::where('status', 'pending')
            ->where('scheduled_at', '<=', $now)
            ->with(['facebookPage.user'])
            ->get();

        $pendingCount = ScheduledPost::where('status', 'pending')->count();
        $this->info("إحصائية: عدد المنشورات pending: {$pendingCount}");
        $this->info("المنشورات الجاهزة للنشر الآن: {$posts->count()}");

        if ($posts->isEmpty()) {
            $this->warn('لا توجد منشورات جاهزة للنشر حالياً.');
            return;
        }

        foreach ($posts as $post) {
            $this->line('');
            $this->info("معالجة المنشور #{$post->id}");
            $this->publishPost($post);
        }

        $this->info('--- انتهت عملية المعالجة ---');
    }

    private function publishPost(ScheduledPost $post)
    {
        try {
            $page = $post->facebookPage;
            
            if (!$page) {
                throw new \Exception('الصفحة غير موجودة');
            }

            if (empty($page->access_token)) {
                throw new \Exception('Access token مفقود للصفحة');
            }

            $this->line("الصفحة: {$page->page_name}");
            $this->line("المستخدم: {$page->user->name}");

            // Prepare data
            $data = [
                'message' => $post->content,
                'access_token' => $page->access_token,
            ];

            // Add media if exists
            if ($post->media_url) {
                $this->line("يحتوي على ميديا: {$post->media_url}");
                
                if ($post->media_type === 'image') {
                    $data['url'] = $post->media_url;
                    $endpoint = "/{$page->page_id}/photos";
                } elseif ($post->media_type === 'video') {
                    $data['file_url'] = $post->media_url;
                    $endpoint = "/{$page->page_id}/videos";
                } else {
                    $endpoint = "/{$page->page_id}/feed";
                }
            } else {
                $endpoint = "/{$page->page_id}/feed";
            }

            // Make request
            $this->line("الإرسال إلى: https://graph.facebook.com/v18.0{$endpoint}");
            
            $response = Http::timeout(30)->post(
                "https://graph.facebook.com/v18.0{$endpoint}",
                $data
            );

            if ($response->successful()) {
                $result = $response->json();
                
                $post->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'fb_post_id' => $result['id'] ?? $result['post_id'] ?? null,
                ]);

                $this->info("✅ تم النشر بنجاح! FB Post ID: {$post->fb_post_id}");
                
            } else {
                $error = $response->json();
                $errorMsg = $error['error']['message'] ?? 'Unknown error';
                
                throw new \Exception($errorMsg);
            }

        } catch (\Exception $e) {
            $this->error("❌ فشل النشر: {$e->getMessage()}");
            
            $post->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to publish post {$post->id}", [
                'error' => $e->getMessage(),
                'post' => $post->toArray(),
            ]);
        }
    }
}