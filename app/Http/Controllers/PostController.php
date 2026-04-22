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
        'page_name'    => 'required|string',
        'content'      => 'required|string',
        'media'        => 'nullable|image|max:5000', 
    ]);

    $page = auth()->user()->pages()
        ->where('page_name', 'LIKE', '%' . trim($request->page_name) . '%')
        ->first();

    $mediaData = null;

    if ($request->hasFile('media')) {

        $path = $request->file('media')->store('posts', 'public');
        
       
        $mediaData = [
            [
                'type' => 'image',
                'path' => $path
            ]
        ];
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

    $apiKey = config('services.gemini.key');
    
   
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(15)->post($url, [
            'contents' => [
                ['parts' => [['text' => "You are a social media expert. Write 3 Facebook post captions about the following idea. IMPORTANT: You must write in the SAME language as the idea. If the idea is in Arabic, write in Arabic. If in English, write in English. The idea is: \"{$request->idea}\". الأول رسمي، الثاني ودّي، الثالث جذاب. أعد النتيجة بصيغة JSON فقط: {\"captions\": [\"...\", \"...\", \"...\"]}"]]]
            ],
            
            'generationConfig' => [
                'response_mime_type' => 'application/json',
            ],
        ]);

        if ($response->failed()) {
          
            \Log::error('Gemini API Error: ' . $response->body());
            return response()->json(['error' => 'Error not conected with Ai!'], 500);
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            return response()->json(['error' => 'error!!'], 500);
        }

        $parsed = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['captions'])) {
            return response()->json(['error' => 'data not correct'], 500);
        }

        return response()->json(['captions' => $parsed['captions']]);

    } catch (\Exception $e) {
        \Log::error('Generation Exception: ' . $e->getMessage());
        return response()->json(['error' => 'Errore Not predicted'], 500);
    }
}
}
