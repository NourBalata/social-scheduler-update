<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeminiService
{
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
private array $failedKeys = [];
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
           if (in_array($key, $this->failedKeys)) continue;

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
                  $this->failedKeys[] = $key;
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

    /**
     * Generate an image using Imagen via Gemini API.
     * Returns the saved Media model or throws an exception.
     */
    public function generateImage(string $prompt, int $userId): \App\Models\Media
    {
        $keys = array_filter(config('services.gemini.keys', []));

        foreach ($keys as $key) {
            try {
                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:predict?key={$key}",
                    [
                        'instances'  => [['prompt' => $prompt]],
                        'parameters' => [
                            'sampleCount'  => 1,
                            'aspectRatio'  => '1:1',
                            'outputOptions' => ['mimeType' => 'image/jpeg'],
                        ],
                    ]
                );

                if ($response->successful()) {
                    $b64 = $response->json('predictions.0.bytesBase64Encoded');

                    if (!$b64) {
                        Log::warning('Imagen: no image data in response', ['body' => $response->body()]);
                        continue;
                    }

                    // Save to storage
                    $filename  = 'ai_' . Str::uuid() . '.jpg';
                    $folder    = 'media-library/' . $userId;
                    $path      = $folder . '/' . $filename;

                    Storage::disk('public')->makeDirectory($folder);
                    Storage::disk('public')->put($path, base64_decode($b64));

                    // Create thumb
                    $thumbDir  = 'media-library/' . $userId . '/thumbs';
                    $thumbName = 'thumb_' . $filename;
                    $thumbPath = $thumbDir . '/' . $thumbName;
                    Storage::disk('public')->makeDirectory($thumbDir);

                    // Simple resize for thumb using GD (no Intervention dependency issues)
                    $imgData = base64_decode($b64);
                    $src     = imagecreatefromstring($imgData);
                    if ($src) {
                        [$w, $h] = [imagesx($src), imagesy($src)];
                        $tw = $th = 300;
                        $thumb = imagecreatetruecolor($tw, $th);
                        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
                        ob_start();
                        imagejpeg($thumb, null, 85);
                        $thumbData = ob_get_clean();
                        Storage::disk('public')->put($thumbPath, $thumbData);
                        imagedestroy($src);
                        imagedestroy($thumb);
                        $width  = $w;
                        $height = $h;
                    } else {
                        $thumbPath = null;
                        $width = $height = null;
                    }

                    $size = strlen($imgData);

                    $media = \App\Models\Media::create([
                        'user_id'           => $userId,
                        'filename'          => $filename,
                        'original_filename' => 'AI Generated - ' . Str::limit($prompt, 40) . '.jpg',
                        'path'              => $path,
                        'disk'              => 'public',
                        'mime_type'         => 'image/jpeg',
                        'type'              => 'image',
                        'size'              => $size,
                        'width'             => $width,
                        'height'            => $height,
                        'thumbnail_path'    => $thumbPath,
                        'metadata'          => ['ai_generated' => true, 'prompt' => Str::limit($prompt, 200)],
                    ]);

                    return $media;
                }

                $errorMsg = $response->json('error.message') ?? '';
                Log::warning("Imagen key failed: {$errorMsg}");

            } catch (\Exception $e) {
                Log::warning('Imagen exception: ' . $e->getMessage());
                continue;
            }
        }

        throw new \Exception('imagen_failed');
    }
}
