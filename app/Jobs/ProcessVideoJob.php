<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\MediaProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 2;
    public $timeout = 600; 
    public $backoff = 120;

    public function __construct(
        protected Media  $media,
        protected string $operation, 
        protected array  $options = []
    ) {

        $this->onQueue('video');
    }

    public function handle(MediaProcessingService $service): void
    {
  
        $media = $this->media->fresh();
        if (!$media) {
            Log::info("ProcessVideoJob: media #{$this->media->id} no longer exists, skipping.");
            return;
        }

        match ($this->operation) {
            'compress'  => $service->compressVideo($media, $this->options),
            'trim'      => $service->trimVideo($media, $this->options['start'], $this->options['end']),
            'thumbnail' => $service->generateVideoThumbnail($media),
            default     => throw new \InvalidArgumentException("Unknown video operation: {$this->operation}"),
        };
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ProcessVideoJob failed for media #{$this->media->id}: " . $e->getMessage());
    }
}