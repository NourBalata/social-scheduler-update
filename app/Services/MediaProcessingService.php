<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;

class MediaProcessingService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function upload(UploadedFile $file, int $userId, ?int $folderId = null): Media
    {
        $type      = $this->detectType($file->getMimeType());
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $folder    = "media/{$userId}/" . now()->format('Y/m');
        $path      = $file->storeAs($folder, $filename, 'public');

        $meta      = [];
        $width     = null;
        $height    = null;
        $duration  = null;
        $thumbPath = null;

        if ($type === 'image') {
            [$width, $height, $thumbPath] = $this->processImage($path, $userId);
        } elseif ($type === 'video') {
            [$duration, $thumbPath, $width, $height] = $this->processVideo($path, $userId);
            $meta['size_original'] = $file->getSize();
        }

        return Media::create([
            'user_id'           => $userId,
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path'              => $path,
            'disk'              => 'public',
            'mime_type'         => $file->getMimeType(),
            'type'              => $type,
            'size'              => $file->getSize(),
            'width'             => $width,
            'height'            => $height,
            'duration'          => $duration,
            'metadata'          => $meta,
            'thumbnail_path'    => $thumbPath,
            'folder_id'         => $folderId,
        ]);
    }


    protected function processImage(string $path, int $userId): array
    {
        $fullPath  = Storage::disk('public')->path($path);
        $image     = $this->imageManager->read($fullPath);
        $width     = $image->width();
        $height    = $image->height();

        // Generate thumbnail
        $thumbName = 'thumb_' . basename($path);
        $thumbDir  = "media/{$userId}/thumbs/" . now()->format('Y/m');
        $thumbFull = Storage::disk('public')->path($thumbDir . '/' . $thumbName);

        @mkdir(dirname($thumbFull), 0755, true);

        $image->scaleDown(400, 400)->save($thumbFull);

        $thumbPath = $thumbDir . '/' . $thumbName;
        Storage::disk('public')->put($thumbPath, file_get_contents($thumbFull));
        @unlink($thumbFull);

        return [$width, $height, $thumbPath];
    }

    protected function processVideo(string $path, int $userId): array
    {
        $duration  = null;
        $thumbPath = null;
        $width     = null;
        $height    = null;

        try {
            $ffmpeg = FFMpeg::create();
            $video  = $ffmpeg->open(Storage::disk('public')->path($path));
            $stream = $video->getStreams()->videos()->first();

            $width    = $stream?->get('width');
            $height   = $stream?->get('height');
            $duration = (int) $video->getFormat()->get('duration');

            // Extract thumbnail at 1 second
            $thumbName = 'thumb_' . pathinfo(basename($path), PATHINFO_FILENAME) . '.jpg';
            $thumbDir  = "media/{$userId}/thumbs/" . now()->format('Y/m');
            $thumbFull = Storage::disk('public')->path($thumbDir . '/' . $thumbName);

            @mkdir(dirname($thumbFull), 0755, true);

            $frame = $video->frame(TimeCode::fromSeconds(1));
            $frame->save($thumbFull);

            $thumbPath = $thumbDir . '/' . $thumbName;
            Storage::disk('public')->put($thumbPath, file_get_contents($thumbFull));
            @unlink($thumbFull);
        } catch (\Exception $e) {
            // 
        }

        return [$duration, $thumbPath, $width, $height];
    }


    public function transform(Media $media, array $operations, bool $saveAsNew = true): Media
    {
        $sourcePath = Storage::disk('public')->path($media->path);
        $image      = $this->imageManager->read($sourcePath);

        foreach ($operations as $op) {
            $image = match ($op['type']) {
                'resize'     => $image->scaleDown($op['width'] ?? null, $op['height'] ?? null),
                'crop'       => $image->crop($op['width'], $op['height'], $op['x'] ?? 0, $op['y'] ?? 0),
                'rotate'     => $image->rotate($op['angle'] ?? 90),
                'flip'       => $op['direction'] === 'h' ? $image->flip() : $image->flop(),
                'brightness' => $image->brightness($op['level'] ?? 0),
                'contrast'   => $image->contrast($op['level'] ?? 0),
                'grayscale'  => $image->greyscale(),
                'blur'       => $image->blur($op['amount'] ?? 5),
                'sharpen'    => $image->sharpen($op['amount'] ?? 5),
                default      => $image,
            };
        }

        return $this->saveEditedImage($image, $media, $saveAsNew);
    }

    public function applyFilter(Media $media, string $filter, bool $saveAsNew = true): Media
    {
        $image = $this->imageManager->read(Storage::disk('public')->path($media->path));

        $image = match ($filter) {
            'grayscale'  => $image->greyscale(),
            'sepia'      => $image->greyscale()->brightness(-10)->contrast(10),
            'vintage'    => $image->greyscale()->brightness(-5)->contrast(5)->blur(1),
            'vivid'      => $image->brightness(10)->contrast(20),
            'cool'       => $image->brightness(-5)->contrast(5),
            'warm'       => $image->brightness(5)->contrast(5),
            'blur'       => $image->blur(3),
            'sharpen'    => $image->sharpen(10),
            'invert'     => $image->invert(),
            default      => $image,
        };

        return $this->saveEditedImage($image, $media, $saveAsNew);
    }

    public function getAvailableFilters(): array
    {
        return [
            ['id' => 'grayscale', 'name' => 'Grayscale',  'icon' => '⬛'],
            ['id' => 'sepia',     'name' => 'Sepia',      'icon' => '🟫'],
            ['id' => 'vintage',   'name' => 'Vintage',    'icon' => '📷'],
            ['id' => 'vivid',     'name' => 'Vivid',      'icon' => '🌈'],
            ['id' => 'cool',      'name' => 'Cool',       'icon' => '❄️'],
            ['id' => 'warm',      'name' => 'Warm',       'icon' => '☀️'],
            ['id' => 'blur',      'name' => 'Blur',       'icon' => '💨'],
            ['id' => 'sharpen',   'name' => 'Sharpen',    'icon' => '🔍'],
            ['id' => 'invert',    'name' => 'Invert',     'icon' => '🔄'],
        ];
    }


    public function addTextOverlay(Media $media, string $text, array $options = []): Media
    {
        $image = $this->imageManager->read(Storage::disk('public')->path($media->path));

        $fontSize  = $options['font_size']  ?? 32;
        $color     = $options['color']      ?? 'ffffff';
        $x         = $options['x']          ?? (int)($image->width() / 2);
        $y         = $options['y']          ?? (int)($image->height() - 60);

      
        $image->text($text, $x, $y, function ($font) use ($fontSize, $color) {
            $font->size($fontSize);
            $font->color($color);
            $font->align('center');
            $font->valign('bottom');
        });

        return $this->saveEditedImage($image, $media, true);
    }

  
    public function addWatermark(
        Media $media,
        string $watermarkPath,
        string $position = 'bottom-right',
        int $opacity = 70,
        int $padding = 15
    ): Media {
        $image     = $this->imageManager->read(Storage::disk('public')->path($media->path));
        $watermark = $this->imageManager->read($watermarkPath);

        // Scale watermark to ~20% of image width
        $wWidth = (int)($image->width() * 0.2);
        $watermark->scaleDown($wWidth);

        $posMap = [
            'top-left'     => 'top-left',
            'top-right'    => 'top-right',
            'bottom-left'  => 'bottom-left',
            'bottom-right' => 'bottom-right',
            'center'       => 'center',
        ];

        $image->place($watermark, $posMap[$position] ?? 'bottom-right', $padding, $padding, $opacity);

        return $this->saveEditedImage($image, $media, true);
    }

    public function compressVideo(Media $media, array $options = []): Media
    {
        $sourcePath = Storage::disk('public')->path($media->path);
        $newName    = Str::uuid() . '_compressed.mp4';
        $outDir     = "media/{$media->user_id}/" . now()->format('Y/m');
        $outPath    = Storage::disk('public')->path($outDir . '/' . $newName);

        @mkdir(dirname($outPath), 0755, true);

        try {
            $ffmpeg  = FFMpeg::create();
            $video   = $ffmpeg->open($sourcePath);
            $format  = new X264();
            $format->setKiloBitrate($options['bitrate'] ?? 1000)
                   ->setAudioKiloBitrate($options['audio_bitrate'] ?? 128);
            $video->save($format, $outPath);

            $sizeReduction = round((1 - filesize($outPath) / $media->size) * 100, 1);

            return Media::create([
                'user_id'           => $media->user_id,
                'filename'          => $newName,
                'original_filename' => pathinfo($media->original_filename, PATHINFO_FILENAME) . '_compressed.mp4',
                'path'              => $outDir . '/' . $newName,
                'disk'              => 'public',
                'mime_type'         => 'video/mp4',
                'type'              => 'video',
                'size'              => filesize($outPath),
                'thumbnail_path'    => $media->thumbnail_path,
                'folder_id'         => $media->folder_id,
                'metadata'          => ['size_reduction' => $sizeReduction . '%'],
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Video compression failed: ' . $e->getMessage());
        }
    }

  
    public function trimVideo(Media $media, float $start, float $end): Media
    {
        $sourcePath = Storage::disk('public')->path($media->path);
        $newName    = Str::uuid() . '_trimmed.mp4';
        $outDir     = "media/{$media->user_id}/" . now()->format('Y/m');
        $outPath    = Storage::disk('public')->path($outDir . '/' . $newName);

        @mkdir(dirname($outPath), 0755, true);

        try {
            $ffmpeg = FFMpeg::create();
            $video  = $ffmpeg->open($sourcePath);
            $clip   = $video->clip(TimeCode::fromSeconds($start), TimeCode::fromSeconds($end - $start));
            $clip->save(new X264(), $outPath);

            return Media::create([
                'user_id'           => $media->user_id,
                'filename'          => $newName,
                'original_filename' => pathinfo($media->original_filename, PATHINFO_FILENAME) . '_trimmed.mp4',
                'path'              => $outDir . '/' . $newName,
                'disk'              => 'public',
                'mime_type'         => 'video/mp4',
                'type'              => 'video',
                'size'              => filesize($outPath),
                'thumbnail_path'    => $media->thumbnail_path,
                'folder_id'         => $media->folder_id,
                'duration'          => (int)($end - $start),
                'metadata'          => ['trimmed_from' => $start, 'trimmed_to' => $end],
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException('Video trim failed: ' . $e->getMessage());
        }
    }

 
    public function delete(Media $media): void
    {
        $media->delete(); // Model handles file deletion
    }

    public function deleteBatch(array $ids, int $userId): int
    {
        $count = 0;
        Media::whereIn('id', $ids)->where('user_id', $userId)->each(function ($media) use (&$count) {
            $media->delete();
            $count++;
        });
        return $count;
    }

    protected function detectType(string $mime): string
    {
        if (str_starts_with($mime, 'image/gif'))  return 'gif';
        if (str_starts_with($mime, 'image/'))     return 'image';
        if (str_starts_with($mime, 'video/'))     return 'video';
        return 'other';
    }

    protected function saveEditedImage($image, Media $original, bool $saveAsNew): Media
    {
        $newName  = Str::uuid() . '.' . pathinfo($original->filename, PATHINFO_EXTENSION);
        $dir      = "media/{$original->user_id}/" . now()->format('Y/m');
        $fullPath = Storage::disk('public')->path($dir . '/' . $newName);

        @mkdir(dirname($fullPath), 0755, true);
        $image->save($fullPath);

        $relativePath = $dir . '/' . $newName;
        Storage::disk('public')->put($relativePath, file_get_contents($fullPath));
        @unlink($fullPath);

        if ($saveAsNew) {
            // Generate thumb for new image
            $thumbName = 'thumb_' . $newName;
            $thumbDir  = "media/{$original->user_id}/thumbs/" . now()->format('Y/m');
            $thumbFull = Storage::disk('public')->path($thumbDir . '/' . $thumbName);
            @mkdir(dirname($thumbFull), 0755, true);

            $this->imageManager->read(Storage::disk('public')->path($relativePath))
                 ->scaleDown(400, 400)
                 ->save($thumbFull);

            $thumbPath = $thumbDir . '/' . $thumbName;
            Storage::disk('public')->put($thumbPath, file_get_contents($thumbFull));
            @unlink($thumbFull);

            return Media::create([
                'user_id'           => $original->user_id,
                'filename'          => $newName,
                'original_filename' => 'edited_' . $original->original_filename,
                'path'              => $relativePath,
                'disk'              => 'public',
                'mime_type'         => $original->mime_type,
                'type'              => $original->type,
                'size'              => Storage::disk('public')->size($relativePath),
                'width'             => $image->width(),
                'height'            => $image->height(),
                'thumbnail_path'    => $thumbPath,
                'folder_id'         => $original->folder_id,
                'metadata'          => ['edited_from' => $original->id],
            ]);
        }

        $original->update([
            'path'    => $relativePath,
            'size'    => Storage::disk('public')->size($relativePath),
            'width'   => $image->width(),
            'height'  => $image->height(),
        ]);

        return $original->fresh();
    }
}