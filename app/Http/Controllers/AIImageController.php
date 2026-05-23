<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AIImageController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'caption' => 'required|string|min:5|max:500',
        ]);

        $user = Auth::user();

    
        if (! $user->hasActivePlan() && ! ($user->currentPlan?->isFree())) {
            return response()->json(['error' => 'Active subscription required.'], 403);
        }

        try {
            $result = $this->gemini->generateImage($request->caption);
            $b64    = $result['b64'];
            $prompt = $result['prompt'] ?? '';

            // Decode and save to Media Library
            $imageData = base64_decode($b64);
            if (! $imageData) {
                throw new \Exception('Invalid image data from AI');
            }

            $userId   = $user->id;
            $filename = 'ai_' . Str::uuid() . '.png';
            $folder   = "media/{$userId}/" . now()->format('Y/m');
            $path     = $folder . '/' . $filename;

            Storage::disk('public')->put($path, $imageData);

     
            $tempPath = Storage::disk('public')->path($path);
            [$width, $height] = @getimagesize($tempPath) ?: [null, null];

       
            $thumbDir  = "media/{$userId}/thumbs/" . now()->format('Y/m');
            $thumbName = 'thumb_' . $filename;
            $thumbPath = $thumbDir . '/' . $thumbName;

            try {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $img     = $manager->read($tempPath);
                $thumbFull = Storage::disk('public')->path($thumbDir . '/' . $thumbName);
                @mkdir(dirname($thumbFull), 0755, true);
                $img->scaleDown(400, 400)->save($thumbFull);
                Storage::disk('public')->put($thumbPath, file_get_contents($thumbFull));
            } catch (\Exception $e) {
                $thumbPath = null;
                Log::warning('AI image thumbnail failed: ' . $e->getMessage());
            }

            $media = Media::create([
                'user_id'           => $userId,
                'filename'          => $filename,
                'original_filename' => 'AI Generated: ' . Str::limit($prompt, 60),
                'path'              => $path,
                'disk'              => 'public',
                'mime_type'         => 'image/png',
                'type'              => 'image',
                'size'              => strlen($imageData),
                'width'             => $width,
                'height'            => $height,
                'metadata'          => ['ai_generated' => true, 'prompt' => $prompt],
                'thumbnail_path'    => $thumbPath,
            ]);

            return response()->json([
                'success'  => true,
                'media_id' => $media->id,
                'url'      => Storage::disk('public')->url($path),
                'thumb'    => $thumbPath ? Storage::disk('public')->url($thumbPath) : Storage::disk('public')->url($path),
                'prompt'   => $prompt,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Image generation failed: ' . $e->getMessage());

            $msg = match ($e->getMessage()) {
                'imagen_failed'      => 'Image generation failed. Try rephrasing your caption.',
                'rate_limit_exceeded'=> 'AI is busy, please try again in a moment.',
                default              => 'Something went wrong generating the image.',
            };

            return response()->json(['error' => $msg], 500);
        }
    }
}