<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AIController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'nullable|string|min:5',
            'idea'   => 'nullable|string|min:5',
        ]);

        $input = $request->input('prompt') ?? $request->input('idea');

        if (!$input || strlen($input) < 5) {
            return response()->json(['success' => false, 'error' => 'Text too short'], 422);
        }

        $isHashtag   = str_contains($input, 'Extract 5-7 hashtags');
        $isAutopilot = str_contains($input, 'content strategist');

        // الأوتوبايلوت والهاشتاق بيبعتوا الـ prompt مباشرة بدون JSON mode
        if ($isHashtag || $isAutopilot) {
            $prompt   = $input;
            $jsonMode = false;
        } else {
            $prompt   = "You are a professional social media manager. 
                         Rewrite this text to be more engaging, professional, and include emojis and hashtags: 
                         {$input}
                         Return ONLY valid JSON: {\"text\": \"...\"}";
            $jsonMode = true;
        }

        try {
            $gemini = new \App\Services\GeminiService();
            $result = $gemini->generate($prompt, $jsonMode);

            if ($jsonMode) {
                $parsed = json_decode($result, true);
                $aiText = $parsed['text'] ?? $result;
            } else {
                $aiText = $result;
            }

            return response()->json([
                'success'  => true,
                'text'     => $aiText,
                'captions' => [$aiText],
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