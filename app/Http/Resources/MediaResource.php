<?php
// ============================================================
// app/Http/Resources/MediaResource.php
// ============================================================
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->original_filename,
            'filename'          => $this->filename,
            'type'              => $this->type,
            'mime_type'         => $this->mime_type,
            'url'               => $this->url,
            'thumbnail_url'     => $this->thumbnail_url,
            'size'              => $this->size,
            'human_size'        => $this->human_size,
            'width'             => $this->width,
            'height'            => $this->height,
            'duration'          => $this->duration,
            'is_template'       => $this->is_template,
            'usage_count'       => $this->usage_count,
            'last_used_at'      => $this->last_used_at?->toISOString(),
            'folder'            => $this->whenLoaded('folder', fn() => [
                'id'    => $this->folder?->id,
                'name'  => $this->folder?->name,
                'color' => $this->folder?->color,
            ]),
            'tags'              => $this->whenLoaded('tags', fn() =>
                $this->tags->map(fn($t) => [
                    'id'    => $t->id,
                    'name'  => $t->name,
                    'color' => $t->color,
                ])
            ),
            'metadata'          => $this->metadata,
            'created_at'        => $this->created_at->toISOString(),
            'updated_at'        => $this->updated_at->toISOString(),
        ];
    }
}