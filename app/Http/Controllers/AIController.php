<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
   public function generate(Request $request)
{
    $request->validate(['prompt' => 'required|string|min:5']);

    $prompt = "You are a professional social media manager. 
               Rewrite this text to be more engaging, professional, and include emojis and hashtags: 
               {$request->prompt}
               Return ONLY valid JSON: {\"text\": \"...\"}";

    try {
        $gemini = new \App\Services\GeminiService();
        $result = $gemini->generate($prompt);

        $parsed = json_decode($result, true);

        return response()->json([
            'success' => true,
            'text'    => $parsed['text'] ?? $result,
        ]);

    } catch (\Exception $e) {
        return match($e->getMessage()) {
            'rate_limit_exceeded' => response()->json([
                'success' => false,
                'error'   => 'AI service is busy, please try again in a minute.'
            ], 429),
            default => response()->json([
                'success' => false,
                'error'   => 'Something went wrong.'
            ], 500),
        };
    }
}
}