<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private static array $failedKeys = [];

    // $jsonMode = true  → يرجع JSON (للكابشن والهاشتاق)
    // $jsonMode = false → يرجع نص حر (للأوتوبايلوت)
    public function generate(string $prompt, bool $jsonMode = true): string
    {
        try {
            return $this->tryGemini($prompt, $jsonMode);
        } catch (\Exception $e) {
            Log::warning('Gemini failed, switching to Groq: ' . $e->getMessage());
        }

        try {
            return $this->tryGroq($prompt, $jsonMode);
        } catch (\Exception $e) {
            Log::error('Groq also failed: ' . $e->getMessage());
        }

        throw new \Exception('rate_limit_exceeded');
    }

    private function tryGemini(string $prompt, bool $jsonMode): string
    {
        $keys = array_filter(config('services.gemini.keys', []));

        foreach ($keys as $key) {
            if (in_array($key, self::$failedKeys)) continue;

            try {
                $body = [
                    'contents'         => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'maxOutputTokens' => 8192,
                        'temperature'     => 0.8,
                    ],
                ];

                // فقط للكابشن نجبر JSON format
                if ($jsonMode) {
                    $body['generationConfig']['response_mime_type'] = 'application/json';
                }

                $response = Http::timeout(60)->post("{$this->geminiUrl}?key={$key}", $body);

                if ($response->successful()) {
                    return $response->json('candidates.0.content.parts.0.text') ?? '';
                }

                $errorMsg = $response->json('error.message') ?? '';

                if (str_contains($errorMsg, 'Quota exceeded') || $response->json('error.code') === 503) {
                    self::$failedKeys[] = $key;
                    continue;
                }

            } catch (\Exception $e) {
                continue;
            }
        }

        throw new \Exception('gemini_exhausted');
    }

    private function tryGroq(string $prompt, bool $jsonMode): string
    {
        $body = [
            'model'       => 'llama-3.3-70b-versatile',
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'max_tokens'  => 8192,
        ];

        if ($jsonMode) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.key'),
                'Content-Type'  => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', $body);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? '';
        }

        throw new \Exception('groq_failed');
    }
}