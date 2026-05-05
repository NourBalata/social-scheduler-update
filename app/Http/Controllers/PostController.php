<?php

namespace App\Http\Controllers;

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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'page_name'        => 'required|string',
            'content'          => 'required|string|max:63206',
            'media'            => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov|max:102400',
            'media_library_id' => 'nullable|exists:media,id',
            'scheduled_at'     => 'nullable|date',
        ]);

        $user = auth()->user();
        $page = $user->facebookPages()
            ->where('page_name', 'LIKE', '%' . trim($request->page_name) . '%')
            ->firstOrFail();

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

                $post = ScheduledPost::create([
                    'user_id'          => $user->id,
                    'facebook_page_id' => $page->id,
                    'content'          => $content,
                    'scheduled_at'     => $publishDate,
                    'status'           => 'pending',
                ]);

                $delay = now()->diffInSeconds($publishDate, false);
                PublishPostJob::dispatch($post)->delay(max(0, $delay));

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
}