<?php

namespace App\Http\Controllers;

use App\Jobs\PublishPostJob;
use App\Models\ScheduledPost;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AutopilotController extends Controller
{
    public function __construct(
        private readonly GeminiService $gemini
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'page_name'      => 'required|string',
            'business_name'  => 'required|string|max:100',
            'industry'       => 'required|string|max:50',
            'audience'       => 'required|string|max:100',
            'goal'           => 'required|string|max:100',
            'tone'           => 'required|in:friendly,formal,humorous,inspiring',
            'posts_per_week' => 'required|integer|min:2|max:7',
        ]);

        $user = auth()->user();
        $requiredPostsCount = $payload['posts_per_week'] * 4;

        if ($user->remainingPostsCount() < $requiredPostsCount) {
            return response()->json([
                'error' => "Your plan balance is insufficient. Required: {$requiredPostsCount}, Available: {$user->remainingPostsCount()}."
            ], 422);
        }

        try {
            $prompt = $this->buildMonthPrompt($payload, $requiredPostsCount);
            $response = $this->gemini->generate($prompt);
            
            $cleanJson = preg_replace('/^```json|```$/m', '', $response);
            $decoded = json_decode(trim($cleanJson), true);

            if (!isset($decoded['posts']) || !is_array($decoded['posts'])) {
                throw new \Exception('invalid_ai_response');
            }

            $formattedPosts = collect($decoded['posts'])
                ->filter(fn($post) => !empty($post['content']) && !empty($post['scheduled_at']))
                ->map(fn($post) => [
                    'content'         => Str::limit(trim($post['content']), 2000, ''),
                    'scheduled_at'    => $post['scheduled_at'],
                    'post_type'       => $post['post_type'] ?? 'educational',
                    'suggested_media' => $post['suggested_media'] ?? null,
                ])
                ->values();

            return response()->json([
                'posts' => $formattedPosts,
                'total' => $formattedPosts->count(),
            ]);

        } catch (\Exception $e) {
            Log::error("Autopilot Generation Error: " . $e->getMessage());
            
            $errorMsg = match ($e->getMessage()) {
                'rate_limit_exceeded' => 'AI service is currently busy. Please retry in a moment.',
                'invalid_ai_response' => 'The AI generated an incompatible format. Please try again.',
                default               => 'An unexpected error occurred during content generation.',
            };

            return response()->json(['error' => $errorMsg], 503);
        }
    }

    public function generateSingle(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'date'          => 'required|date_format:Y-m-d',
            'post_type'     => 'required|in:educational,promotional,entertainment,engagement',
            'business_name' => 'required|string|max:100',
            'industry'      => 'required|string|max:50',
            'audience'      => 'required|string|max:100',
            'tone'          => 'required|in:friendly,formal,humorous,inspiring',
        ]);

        try {
            $prompt = $this->buildSinglePrompt($payload);
            $response = $this->gemini->generate($prompt);
            
            $cleanJson = preg_replace('/^```json|```$/m', '', $response);
            $decoded = json_decode(trim($cleanJson), true);

            if (empty($decoded['content'])) {
                return response()->json(['error' => 'AI failed to produce content.'], 500);
            }

            return response()->json([
                'content'        => $decoded['content'],
                'hashtags'       => $decoded['hashtags'] ?? [],
                'suggested_time' => $decoded['suggested_time'] ?? '18:00',
                'post_type'      => $payload['post_type'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'AI Service Unavailable.'], 503);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'page_name'            => 'required|string',
            'posts'                => 'required|array|min:1|max:31',
            'posts.*.content'      => 'required|string|max:63206',
            'posts.*.scheduled_at' => 'required|date|after:now',
            'posts.*.post_type'    => 'nullable|in:educational,promotional,entertainment,engagement,manual',
        ]);

        $user = auth()->user();
        $page = $user->facebookPages()
            ->where('page_name', 'LIKE', "%" . trim($request->page_name) . "%")
            ->firstOrFail();

        $scheduledCount = 0;
        $skippedCount   = 0;

        foreach ($request->posts as $postData) {
            if ($user->remainingPostsCount() <= 0) {
                $skippedCount++;
                continue;
            }

            $scheduledAt = Carbon::parse($postData['scheduled_at']);
            
            $post = ScheduledPost::create([
                'user_id'          => $user->id,
                'facebook_page_id' => $page->id,
                'content'          => $postData['content'],
                'post_type'        => $postData['post_type'] ?? 'manual',
                'scheduled_at'     => $scheduledAt,
                'status'           => 'pending',
            ]);

            PublishPostJob::dispatch($post)->delay(now()->diffInSeconds($scheduledAt));
            $scheduledCount++;
        }

        return response()->json([
            'message'   => "Successfully scheduled {$scheduledCount} posts.",
            'scheduled' => $scheduledCount,
            'skipped'   => $skippedCount,
        ]);
    }

    public function confirmSingle(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'page_name'    => 'required|string',
            'content'      => 'required|string|max:63206',
            'scheduled_at' => 'required|date|after:now',
            'post_type'    => 'nullable|in:educational,promotional,entertainment,engagement,manual',
        ]);

        $user = auth()->user();

        if ($user->remainingPostsCount() <= 0) {
            return response()->json(['error' => 'Plan limit reached.'], 422);
        }

        $page = $user->facebookPages()
            ->where('page_name', 'LIKE', "%" . trim($payload['page_name']) . "%")
            ->firstOrFail();

        $scheduledAt = Carbon::parse($payload['scheduled_at']);

        $post = ScheduledPost::create([
            'user_id'          => $user->id,
            'facebook_page_id' => $page->id,
            'content'          => $payload['content'],
            'post_type'        => $payload['post_type'] ?? 'manual',
            'scheduled_at'     => $scheduledAt,
            'status'           => 'pending',
        ]);

        PublishPostJob::dispatch($post)->delay(now()->diffInSeconds($scheduledAt));

        return response()->json([
            'success' => true,
            'event'   => [
                'title' => Str::limit($post->content, 25),
                'start' => $post->scheduled_at->toIso8601String(),
                'color' => '#3b82f6',
                'extendedProps' => [
                    'status'    => 'pending',
                    'page'      => $page->page_name,
                    'content'   => $post->content,
                    'post_type' => $post->post_type,
                ],
            ],
        ]);
    }

    private function buildMonthPrompt(array $data, int $count): string
    {
        $start = now()->addDay()->toDateString();
        $end   = now()->addDay()->addDays(28)->toDateString();

        return "Act as a Senior Social Media Expert. Generate a Facebook content strategy with {$count} posts from {$start} to {$end}.
        
        Business: {$data['business_name']} ({$data['industry']})
        Audience: {$data['audience']}
        Goal: {$data['goal']}
        Tone: {$data['tone']}

        Requirements:
        1. Language: English.
        2. Mix: 40% Educational, 25% Promo, 25% Entertaining, 10% Engaging.
        3. Timing: 09:00, 12:00, 18:00, or 21:00 only.
        4. Length: 80-200 words with emojis and hashtags.

        Output strictly as JSON:
        {
            \"posts\": [
                {
                    \"content\": \"string\",
                    \"scheduled_at\": \"YYYY-MM-DD HH:MM:SS\",
                    \"post_type\": \"educational|promotional|entertainment|engagement\",
                    \"suggested_media\": \"string\"
                }
            ]
        }";
    }

    private function buildSinglePrompt(array $data): string
    {
        $formattedDate = Carbon::parse($data['date'])->format('l, F j, Y');

        return "Write a high-quality Facebook post in English for {$formattedDate}.
        
        Context: {$data['business_name']} ({$data['industry']})
        Type: {$data['post_type']}
        Tone: {$data['tone']}
        Audience: {$data['audience']}

        Guidelines: 80-150 words, include emojis and a hashtag block. 
        Suggested time: Select from [09:00, 12:00, 18:00, 21:00].

        Output strictly as JSON:
        {
            \"content\": \"string\",
            \"hashtags\": [\"#tag1\"],
            \"suggested_time\": \"HH:MM\"
        }";
    }
}