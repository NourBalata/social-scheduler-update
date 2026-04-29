<?php

namespace App\Http\Controllers;

use App\Models\ScheduledPost;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PostController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'page_name'        => 'required|string',
        'content'          => 'required|string',
        'media'            => 'nullable|file|max:102400',
        'media_library_id' => 'nullable|exists:media_library,id',
    ]);

    $page = auth()->user()->pages()
        ->where('page_name', 'LIKE', '%' . trim($request->page_name) . '%')
        ->first();

    $mediaData = null;

    if ($request->hasFile('media')) {
        // رفع مباشر
        $path = $request->file('media')->store('posts', 'public');
        $mediaData = [['type' => 'image', 'path' => $path]];

    } elseif ($request->filled('media_library_id')) {
    
        $media = \App\Models\Media::where('id', $request->media_library_id)
                                  ->where('user_id', auth()->id())
                                  ->first();
        if ($media) {
            $mediaData = [['type' => $media->type, 'path' => $media->path]];
            $media->incrementUsage();
        }
    }

    \App\Models\ScheduledPost::create([
        'user_id'          => auth()->id(),
        'facebook_page_id' => $page->id,
        'content'          => $request->content,
        'media'            => $mediaData,
        'scheduled_at'     => $request->scheduled_at ?? now(),
        'status'           => 'pending',
    ]);

    return back()->with('success', "Done!");
}

public function storeAnotherPage(Request $request)
{
    $request->validate([
        'page_id'           => 'required|string',
        'page_name'         => 'required|string',
        'page_access_token' => 'required|string',
    ]);
    auth()->user()->facebookPages()->create([
        'page_id'          => $request->page_id,
        'page_name'        => $request->page_name,
        'access_token'     => $request->page_access_token,
        'is_active'        => true,
        'token_expires_at' => now()->addDays(60),
    ]);

    return back()->with('success', 'done.');
}
public function generateCaption(Request $request)
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
        $gemini = new \App\Services\GeminiService();
        $text   = $gemini->generate($prompt);
        $parsed = json_decode($text, true);

        if (!isset($parsed['captions'])) {
            return response()->json(['error' => 'Invalid response format'], 500);
        }

        return response()->json([
            'captions'  => $parsed['captions'],
            'hashtags'  => $parsed['hashtags'] ?? [],
        ]);

    } catch (\Exception $e) {
        return match($e->getMessage()) {
            'rate_limit_exceeded' => response()->json(['error' => 'AI busy, try again in a minute.'], 429),
            default               => response()->json(['error' => 'Something went wrong.'], 500),
        };
    }
}

public function bulkSchedule(Request $request)
{
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt|max:2048',
    ]);

    $file  = $request->file('csv_file');
    $count = 0;
    $errors = [];
    $user  = auth()->user();

    // ← نستخدم SplFileObject بدل file() عشان نتعامل مع encoding أحسن
    $csv = new \SplFileObject($file->getRealPath());
    $csv->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
    $csv->setCsvControl(',');

    foreach ($csv as $index => $cols) {
        // تخطى الـ header
        if ($index === 0) continue;

        // تخطى الصفوف الفاضية
        if (!is_array($cols) || count($cols) < 3) continue;

        $page_name    = trim($cols[0]);
        $content      = trim($cols[1]); // ← كانت [2] وهي غلط، content هي العمود الثاني
        $scheduled_at = trim($cols[2]); // ← كانت [13] وهي البق الأساسي

        if (empty($page_name) || empty($content) || empty($scheduled_at)) continue;

        if ($user->remainingPostsCount() <= 0) {
            $errors[] = "You've reached your posts limit.";
            break;
        }

        $page = $user->pages()
            ->where('page_name', 'LIKE', '%' . $page_name . '%')
            ->first();

        if (!$page) {
            $errors[] = "Row " . ($index + 1) . ": Page '{$page_name}' not found.";
            continue;
        }

        try {
            // جرب format-ين — m/d/Y H:i أو Y-m-d H:i
            try {
                $publishDate = \Carbon\Carbon::createFromFormat('n/j/Y H:i', $scheduled_at);
            } catch (\Exception $e) {
                $publishDate = \Carbon\Carbon::parse($scheduled_at);
            }

            if ($publishDate->isPast()) {
                $publishDate = now()->addMinutes(2);
            }

            $post = \App\Models\ScheduledPost::create([
                'user_id'          => $user->id,
                'facebook_page_id' => $page->id,
                'content'          => $content,
                'scheduled_at'     => $publishDate,
                'status'           => 'pending',
            ]);

            $delay = now()->diffInSeconds($publishDate, false);
            \App\Jobs\PublishPostJob::dispatch($post)->delay($delay > 0 ? $delay : 0);

            $count++;

        } catch (\Exception $e) {
            $errors[] = "Row " . ($index + 1) . ": Date error '{$scheduled_at}' — " . $e->getMessage();
        }
    }

    return back()
        ->with('success', "Done! {$count} post(s) scheduled.")
        ->withErrors($errors);
}
}