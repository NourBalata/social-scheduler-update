<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media_library';

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'path',
        'disk',
        'mime_type',
        'type',
        'size',
        'width',
        'height',
        'duration',
        'metadata',
        'thumbnail_path',
        'folder_id',
        'is_template',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_template' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $appends = ['url', 'thumbnail_url', 'human_size'];

    // ──────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_tag_pivot', 'media_id', 'tag_id');
    }

    // ──────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return $this->type === 'image' ? $this->url : null;
        }

        return Storage::disk($this->disk)->url($this->thumbnail_path);
    }

    public function getHumanSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    // ──────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeInFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopeWithTags($query, array $tagIds)
    {
        return $query->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('media_tags.id', $tagIds);
        });
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ──────────────────────────────────────────────────────────────
    // Methods
    // ──────────────────────────────────────────────────────────────

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function delete(): ?bool
    {
        // Delete physical files
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }

        if ($this->thumbnail_path && Storage::disk($this->disk)->exists($this->thumbnail_path)) {
            Storage::disk($this->disk)->delete($this->thumbnail_path);
        }

        return parent::delete();
    }
}