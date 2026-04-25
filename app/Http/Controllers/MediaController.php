<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Http\Requests\Media\TransformMediaRequest;
use App\Http\Requests\Media\FilterMediaRequest;
use App\Http\Requests\Media\TextOverlayRequest;
use App\Http\Requests\Media\WatermarkRequest;
use App\Http\Requests\Media\CompressVideoRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\MediaCollection;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\MediaTag;
use App\Services\MediaProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
{
    public function __construct(
        protected MediaProcessingService $processingService
    ) {}


    public function index(Request $request): JsonResponse
    {
        $query = Media::where('user_id', Auth::id())
            ->with(['tags', 'folder']);

    
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('tags')) {
            $tagIds = explode(',', $request->tags);
            $query->withTags($tagIds);
        }

        if ($request->boolean('templates_only')) {
            $query->templates();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('tags', fn($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

       
        $sortField     = in_array($request->sort_by, ['created_at', 'size', 'original_filename', 'usage_count'])
            ? $request->sort_by
            : 'created_at';
        $sortDirection = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDirection);

     
        $perPage = min((int) $request->get('per_page', 24), 100);
        $media   = $query->paginate($perPage);

        return response()->json([
            'data'  => MediaResource::collection($media->items()),
            'meta'  => [
                'current_page' => $media->currentPage(),
                'last_page'    => $media->lastPage(),
                'per_page'     => $media->perPage(),
                'total'        => $media->total(),
            ],
        ]);
    }


    public function show(Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $media->load(['tags', 'folder']);
        $media->incrementUsage();

        return response()->json(['data' => new MediaResource($media)]);
    }

 

    public function upload(UploadMediaRequest $request): JsonResponse
    {
        $media = $this->processingService->upload(
            $request->file('file'),
            Auth::id(),
            $request->folder_id
        );

        if ($request->filled('tags')) {
            $tagIds = $this->resolveTagIds($request->tags, Auth::id());
            $media->tags()->sync($tagIds);
        }

        $media->load(['tags', 'folder']);

        return response()->json([
            'message' => 'uploade file done',
            'data'    => new MediaResource($media),
        ], 201);
    }

    public function uploadBatch(Request $request): JsonResponse
    {
        $request->validate([
            'files'     => 'required|array|max:20',
            'files.*'   => 'required|file|max:102400|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv',
            'folder_id' => 'nullable|exists:media_folders,id',
            'tags'      => 'nullable|array',
        ]);

        $results = [];
        $errors  = [];

        DB::beginTransaction();
        try {
            foreach ($request->file('files') as $file) {
                try {
                    $media = $this->processingService->upload($file, Auth::id(), $request->folder_id);

                    if ($request->filled('tags')) {
                        $tagIds = $this->resolveTagIds($request->tags, Auth::id());
                        $media->tags()->sync($tagIds);
                    }

                    $results[] = new MediaResource($media->load('tags'));
                } catch (\Exception $e) {
                    $errors[] = [
                        'file'  => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ];
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'failed to upload file', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message'   => 'upload done ' . count($results) . ' file',
            'data'      => $results,
            'errors'    => $errors,
            'uploaded'  => count($results),
            'failed'    => count($errors),
        ], 201);
    }

 
    public function transform(TransformMediaRequest $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $edited = $this->processingService->transform(
            $media,
            $request->validated('operations'),
            $request->boolean('save_as_new', true)
        );

        return response()->json([
            'message' => 'تم تعديل الصورة بنجاح',
            'data'    => new MediaResource($edited->load('tags')),
        ]);
    }

    public function applyFilter(FilterMediaRequest $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $edited = $this->processingService->applyFilter(
            $media,
            $request->validated('filter'),
            $request->boolean('save_as_new', true)
        );

        return response()->json([
            'message' => 'done filter!',
            'data'    => new MediaResource($edited->load('tags')),
        ]);
    }

    public function filters(): JsonResponse
    {
        return response()->json([
            'data' => $this->processingService->getAvailableFilters(),
        ]);
    }


    public function addText(TextOverlayRequest $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $edited = $this->processingService->addTextOverlay(
            $media,
            $request->validated('text'),
            $request->validated('options', [])
        );

        return response()->json([
            'message' => 'done enter text!',
            'data'    => new MediaResource($edited->load('tags')),
        ]);
    }

 
    public function addWatermark(WatermarkRequest $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $watermarkPath = $request->hasFile('watermark')
            ? $request->file('watermark')->getRealPath()
            : public_path('images/watermark.png');

        $edited = $this->processingService->addWatermark(
            $media,
            $watermarkPath,
            $request->validated('position', 'bottom-right'),
            $request->validated('opacity', 70),
            $request->validated('padding', 15)
        );

        return response()->json([
            'message' => 'donee!!',
            'data'    => new MediaResource($edited->load('tags')),
        ]);
    }

    public function compressVideo(CompressVideoRequest $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $compressed = $this->processingService->compressVideo($media, $request->validated());

        return response()->json([
            'message'          => 'done compose vedio!',
            'data'             => new MediaResource($compressed),
            'size_reduction'   => $compressed->metadata['size_reduction'] ?? null,
        ]);
    }


    public function trimVideo(Request $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $request->validate([
            'start' => 'required|numeric|min:0',
            'end'   => 'required|numeric|gt:start',
        ]);

        $trimmed = $this->processingService->trimVideo(
            $media,
            (float) $request->start,
            (float) $request->end
        );

        return response()->json([
            'message' => 'Cut vedio is done',
            'data'    => new MediaResource($trimmed),
        ]);
    }


    public function folders(): JsonResponse
    {
        $folders = MediaFolder::where('user_id', Auth::id())
            ->withCount('media')
            ->with('children')
            ->whereNull('parent_id')
            ->get();

        return response()->json(['data' => $folders]);
    }

    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'color'     => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $folder = MediaFolder::create([
            'user_id'   => Auth::id(),
            'name'      => $request->name,
            'color'     => $request->color,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'message' => 'create folder done',
            'data'    => $folder,
        ], 201);
    }


    public function deleteFolder(MediaFolder $folder): JsonResponse
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        Media::where('folder_id', $folder->id)->update(['folder_id' => null]);
        $folder->delete();

        return response()->json(['message' => ' delete folder done ']);
    }

    public function moveToFolder(Request $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $request->validate([
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $media->update(['folder_id' => $request->folder_id]);

        return response()->json([
            'message' => 'replace folder done',
            'data'    => new MediaResource($media->load(['tags', 'folder'])),
        ]);
    }

   
    public function tags(): JsonResponse
    {
        $tags = MediaTag::where('user_id', Auth::id())
            ->withCount('media')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $tags]);
    }

    public function createTag(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag = MediaTag::firstOrCreate(
            ['user_id' => Auth::id(), 'name' => $request->name],
            ['color'   => $request->color ?? '#6366f1']
        );

        return response()->json([
            'message' => 'create tag done',
            'data'    => $tag,
        ], 201);
    }

   
    public function syncTags(Request $request, Media $media): JsonResponse
    {
        $this->authorizeMedia($media);

        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'string|max:50',
        ]);

        $tagIds = $this->resolveTagIds($request->tags, Auth::id());
        $media->tags()->sync($tagIds);

        return response()->json([
            'message' => 'update tages done',
            'data'    => $media->load('tags'),
        ]);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorizeMedia($media);
        $this->processingService->delete($media);

        return response()->json(['message' => 'delete file done']);
    }

    public function destroyBatch(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array|max:50',
            'ids.*' => 'integer|exists:media_library,id',
        ]);

        $count = $this->processingService->deleteBatch($request->ids, Auth::id());

        return response()->json([
            'message' => "delete file done ",
            'deleted' => $count,
        ]);
    }


    public function stats(): JsonResponse
    {
        $userId = Auth::id();

        $stats = Media::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_files,
                SUM(size) as total_size,
                COUNT(CASE WHEN type = "image" THEN 1 END) as images,
                COUNT(CASE WHEN type = "video" THEN 1 END) as videos,
                COUNT(CASE WHEN type = "gif" THEN 1 END) as gifs,
                COUNT(CASE WHEN is_template = 1 THEN 1 END) as templates
            ')
            ->first();

        $foldersCount = MediaFolder::where('user_id', $userId)->count();
        $tagsCount    = MediaTag::where('user_id', $userId)->count();

        return response()->json([
            'data' => [
                'total_files'   => $stats->total_files,
                'total_size'    => $stats->total_size,
                'human_size'    => $this->formatBytes($stats->total_size ?? 0),
                'images'        => $stats->images,
                'videos'        => $stats->videos,
                'gifs'          => $stats->gifs,
                'templates'     => $stats->templates,
                'folders'       => $foldersCount,
                'tags'          => $tagsCount,
            ],
        ]);
    }

    protected function authorizeMedia(Media $media): void
    {
        if ($media->user_id !== Auth::id()) {
            abort(403, 'unauthrized');
        }
    }

    protected function resolveTagIds(array $tags, int $userId): array
    {
        return collect($tags)->map(function ($tag) use ($userId) {
            $model = MediaTag::firstOrCreate(
                ['user_id' => $userId, 'name' => trim($tag)],
                ['color'   => '#' . substr(md5($tag), 0, 6)]
            );
            return $model->id;
        })->toArray();
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}