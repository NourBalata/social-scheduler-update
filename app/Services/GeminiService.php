<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private static array $failedKeys = [];

    public function generate(string $prompt): string
    {
        // جرب Gemini أولاً
        try {
            return $this->tryGemini($prompt);
        } catch (\Exception $e) {
            Log::warning('Gemini failed, switching to Groq: ' . $e->getMessage());
        }

        // إذا Gemini فشل — جرب Groq
        try {
            return $this->tryGroq($prompt);
        } catch (\Exception $e) {
            Log::error('Groq also failed: ' . $e->getMessage());
        }

        throw new \Exception('rate_limit_exceeded');
    }

    private function tryGemini(string $prompt): string
    {
        $keys = array_filter(config('services.gemini.keys', []));

        foreach ($keys as $key) {
            if (in_array($key, self::$failedKeys)) continue;

            try {
                $response = Http::timeout(20)->post("{$this->geminiUrl}?key={$key}", [
                    'contents'         => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['response_mime_type' => 'application/json'],
                ]);

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

    private function tryGroq(string $prompt): string
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.key'),
                'Content-Type'  => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'           => 'llama-3.3-70b-versatile',
                'messages'        => [['role' => 'user', 'content' => $prompt]],
                'max_tokens'      => 800,
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content') ?? '';
        }

        throw new \Exception('groq_failed');
    }
}