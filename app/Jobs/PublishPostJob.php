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

    // عدد مرات إعادة المحاولة في حال فشل الـ API
    public $tries = 3;

    // كم ثانية ننتظر قبل إعادة المحاولة
    public $backoff = 60;

    protected $post;

    public function __construct(ScheduledPost $post)
    {
        $this->post = $post;
    }

    public function handle(SocialMediaProvider $facebookService): void
    {
        // 1. تأكدي إن البوست لسه "pending" (حماية من التكرار)
        if ($this->post->status !== 'pending') return;

        try {
            // 2. سحب توكن الصفحة من العلاقة اللي عملناها
            $page = $this->post->facebookPage;

            if (!$page || !$page->isTokenValid()) {
                throw new Exception("توكن الصفحة غير صالح أو الصفحة محذوفة.");
            }

            // 3. النشر الفعلي عبر السيرفس
            $fbPostId = $facebookService->post($page->access_token, $page->page_id, [
                'content' => $this->post->content,
                'media'   => $this->post->media, // لو السيرفس بيدعم الميديا
            ]);

            // 4. تحديث الحالة لـ Success
            $this->post->markAsPublished($fbPostId);

        } catch (Exception $e) {
            // 5. تسجيل الفشل وتحديث حالة البوست
            $this->post->markAsFailed($e->getMessage());
            
            // نترك الـ Job يفشل عشان الـ Queue يعمل Retry لو لسه في محاولات
            throw $e; 
        }
    }
}