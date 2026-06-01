<?php

namespace App\Jobs;

use App\Models\RepostRule;
use App\Models\ScheduledPost;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateRepostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 180, 300];
    public $timeout = 60;

    public function __construct(
        protected RepostRule $rule
    ) {}

    public function handle(GeminiService $gemini): void
    {
        // تأكد إن القاعدة لسا نشطة
        if (! $this->rule->is_active) {
            return;
        }

        // اطلب من الـ AI يعيد كتابة المحتوى بأسلوب مختلف
        $prompt = $this->buildPrompt($this->rule->original_content);

        try {
            $raw        = $gemini->generate($prompt, false);
            $newContent = trim($raw);
        } catch (\Exception $e) {
            Log::error('GenerateRepostJob: AI failed', [
                'rule_id' => $this->rule->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }

        // أنشئ scheduled post جديد بالمحتوى الجديد
        ScheduledPost::create([
            'user_id'          => $this->rule->user_id,
            'facebook_page_id' => $this->rule->facebook_page_id,
            'content'          => $newContent,
            'media'            => $this->rule->originalPost->media, // نفس الميديا
            'scheduled_at'     => now()->addMinutes(5),
            'status'           => 'pending',
        ]);

        // حدّث القاعدة
        $this->rule->markAsReposted();

        Log::info('GenerateRepostJob: repost created', [
            'rule_id'      => $this->rule->id,
            'repost_count' => $this->rule->repost_count + 1,
        ]);
    }

    public function failed(\Exception $exception): void
    {
        Log::error('GenerateRepostJob: failed after all retries', [
            'rule_id' => $this->rule->id,
            'error'   => $exception->getMessage(),
        ]);
    }

    private function buildPrompt(string $originalContent): string
    {
        return "You are a social media copywriter.\n"
            . "Rewrite the following Facebook post in a fresh, engaging way.\n"
            . "Keep the same topic and key message, but use different wording, structure, and tone.\n"
            . "Do NOT add hashtags unless the original had them.\n"
            . "Return only the rewritten post text, nothing else.\n\n"
            . "Original post:\n{$originalContent}";
    }
}