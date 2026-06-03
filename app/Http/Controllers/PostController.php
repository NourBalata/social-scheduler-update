<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Jobs\PublishPostJob;
use App\Models\Media;
use App\Models\ScheduledPost;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    public function store(StorePostRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->canSchedulePost()) {
            return back()->with('error', 'You have reached your monthly post limit. Please upgrade your plan.');
        }

        $page = $user->facebookPages()->findOrFail($request->facebook_page_id);

        $mediaData = $this->resolveMedia($request, $user->id);

        ScheduledPost::create([
            'user_id'          => $user->id,
            'facebook_page_id' => $page->id,
            'content'          => $request->content,
            'media'            => $mediaData,
            'scheduled_at'     => $request->scheduled_at ?? now(),
            'status'           => 'pending',
        ]);

        return back()->with('success', 'Post scheduled successfully.');
    }

    public function generateCaption(Request $request): JsonResponse
    {
        $request->validate(['idea' => 'required|string|max:300']);

        $prompt = "You are a social media expert. Write 3 Facebook post captions about: \"{$request->idea}\".
                   Write in the SAME language as the idea. No hashtags inside the captions.
                   Also provide 6 relevant hashtags separately.
                   Return ONLY valid JSON:
                   {
                     \"captions\": [\"...\", \"...\", \"...\"],
                     \"hashtags\": [\"#tag1\", \"#tag2\", \"#tag3\", \"#tag4\", \"#tag5\", \"#tag6\"]
                   }";

        try {
            $text   = $this->gemini->generate($prompt);
            $parsed = json_decode($text, true);

            if (! isset($parsed['captions'])) {
                return response()->json(['error' => 'Invalid response format.'], 500);
            }

            return response()->json([
                'captions' => $parsed['captions'],
                'hashtags' => $parsed['hashtags'] ?? [],
            ]);

        } catch (\Exception $e) {
            return match ($e->getMessage()) {
                'rate_limit_exceeded' => response()->json(['error' => 'AI is busy, please try again in a moment.'], 429),
                default               => response()->json(['error' => 'Something went wrong.'], 500),
            };
        }
    }

    public function bulkSchedule(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $user   = auth()->user();
        $count  = 0;
        $errors = [];

        $csv = new \SplFileObject($request->file('csv_file')->getRealPath());
        $csv->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $csv->setCsvControl(',');

        foreach ($csv as $index => $cols) {
            if ($index === 0) continue; 
            if (! is_array($cols) || count($cols) < 3) continue;

            [$pageName, $content, $scheduledAt] = array_map('trim', $cols);

            if (empty($pageName) || empty($content) || empty($scheduledAt)) continue;

            if ($user->remainingPostsCount() <= 0) {
                $errors[] = "Post limit reached. Stopped at row {$index}.";
                break;
            }

            $page = $user->facebookPages()
                ->where('page_name', 'LIKE', "%{$pageName}%")
                ->first();

            if (! $page) {
                $errors[] = "Row {$index}: Page '{$pageName}' not found.";
                continue;
            }

            try {
                $publishDate = $this->parseDate($scheduledAt);

              $exists = ScheduledPost::where('user_id', $user->id)
    ->where('facebook_page_id', $page->id)
    ->where('content', $content)
    ->where('scheduled_at', $publishDate)
    ->exists();

if ($exists) {
    $errors[] = "Row {$index}: منشور مكرر، تم تخطيه.";
    continue;
}

$post = ScheduledPost::create([
    'user_id'          => $user->id,
    'facebook_page_id' => $page->id,
    'content'          => $content,
    'scheduled_at'     => $publishDate,
    'status'           => 'pending',
]);

                // Delay the job until the scheduled time (same pattern as AutopilotController)
                $delaySeconds = max(0, now()->diffInSeconds($publishDate, false));
                PublishPostJob::dispatch($post)->delay($delaySeconds);

                $count++;

            } catch (\Exception $e) {
                $errors[] = "Row {$index}: Date error '{$scheduledAt}' — {$e->getMessage()}";
            }
        }

        return back()
            ->with('success', "{$count} post(s) scheduled successfully.")
            ->withErrors($errors);
    }


    private function resolveMedia(Request $request, int $userId): ?array
    {
        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('posts', 'public');
            $mime = $request->file('media')->getMimeType();
            $type = str_starts_with($mime, 'video') ? 'video' : 'image';

            return [['type' => $type, 'path' => $path]];
        }

        if ($request->filled('media_library_id')) {
            $media = Media::where('id', $request->media_library_id)
                ->where('user_id', $userId)
                ->first();

            if ($media) {
                $media->incrementUsage();
                return [['type' => $media->type, 'path' => $media->path]];
            }
        }

        return null;
    }

    private function parseDate(string $date): Carbon
    {
        try {
            $parsed = Carbon::createFromFormat('n/j/Y H:i', $date);
        } catch (\Exception) {
            $parsed = Carbon::parse($date);
        }

        return $parsed->isPast() ? now()->addMinutes(2) : $parsed;
    }


    public function reschedule(Request $request, \App\Models\ScheduledPost $post): \Illuminate\Http\JsonResponse
{
  
    if ($post->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    if ($post->status !== 'pending') {
        return response()->json(['error' => 'Only pending posts can be rescheduled.'], 422);
    }

 $request->validate([
    'scheduled_at' => 'required|date|after:now',
]);

$post->update([

    'scheduled_at' => \Carbon\Carbon::parse($request->scheduled_at)->utc(),
]);

    return response()->json(['success' => true]);
}

public function bestTime(): JsonResponse
{
    $user = auth()->user();


    $posts = $user->scheduledPosts()
        ->where('status', 'published')
        ->whereNotNull('published_at')
        ->with('analytics')
        ->get()
        ->filter(fn($p) => $p->analytics !== null);


    if ($posts->count() < 5) {
        return response()->json([
            'enough_data' => false,
            'best_hours'  => [9, 12, 18, 21],
            'best_days'   => ['Monday', 'Wednesday', 'Friday'],
            'message'     => 'Not enough data yet. Publish more posts to get personalized suggestions.',
        ]);
    }


    $byHour = $posts->groupBy(fn($p) => $p->published_at->hour)
        ->map(fn($group) => round($group->avg(fn($p) => $p->analytics->reach)))
        ->sortDesc();

    $bestHours = $byHour->take(3)->keys()->values()->toArray();

    $byDay = $posts->groupBy(fn($p) => $p->published_at->format('l'))
        ->map(fn($group) => round($group->avg(fn($p) => $p->analytics->reach)))
        ->sortDesc();

    $bestDays = $byDay->take(3)->keys()->values()->toArray();


    $byType = $posts->groupBy(fn($p) => empty($p->media) ? 'text' : (($p->media[0]['type'] ?? 'text')))
        ->map(fn($group) => round($group->avg(fn($p) => $p->analytics->reach)))
        ->sortDesc();

    return response()->json([
        'enough_data'      => true,
        'best_hours'       => $bestHours,
        'best_days'        => $bestDays,
        'best_media_type'  => $byType->keys()->first() ?? 'text',
        'hours_breakdown'  => $byHour->toArray(),
        'days_breakdown'   => $byDay->toArray(),
    ]);
}

}