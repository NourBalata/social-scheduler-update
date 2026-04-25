<?php
// ============================================================
// UploadMediaRequest.php
// ============================================================
namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'      => 'required|file|max:102400|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,wmv',
            'folder_id' => 'nullable|exists:media_folders,id',
            'tags'      => 'nullable|array',
            'tags.*'    => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يرجى اختيار ملف',
            'file.max'      => 'حجم الملف يجب أن لا يتجاوز 100MB',
            'file.mimes'    => 'نوع الملف غير مدعوم',
        ];
    }
}


<?php
// ============================================================
// TransformMediaRequest.php
// ============================================================
namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class TransformMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'operations'             => 'required|array|min:1',
            'operations.crop'        => 'sometimes|array',
            'operations.crop.width'  => 'required_with:operations.crop|integer|min:1',
            'operations.crop.height' => 'required_with:operations.crop|integer|min:1',
            'operations.crop.x'      => 'nullable|integer|min:0',
            'operations.crop.y'      => 'nullable|integer|min:0',
            'operations.resize'        => 'sometimes|array',
            'operations.resize.width'  => 'nullable|integer|min:1|max:5000',
            'operations.resize.height' => 'nullable|integer|min:1|max:5000',
            'operations.rotate'        => 'sometimes|array',
            'operations.rotate.angle'  => 'required_with:operations.rotate|numeric',
            'operations.flip'          => 'sometimes|array',
            'operations.flip.mode'     => 'required_with:operations.flip|in:h,v',
            'operations.brightness'    => 'sometimes|array',
            'operations.brightness.level' => 'required_with:operations.brightness|integer|between:-100,100',
            'operations.contrast'      => 'sometimes|array',
            'operations.contrast.level' => 'required_with:operations.contrast|integer|between:-100,100',
            'operations.blur'          => 'sometimes|array',
            'operations.blur.amount'   => 'required_with:operations.blur|integer|between:1,100',
            'operations.sharpen'       => 'sometimes|array',
            'operations.sharpen.amount' => 'required_with:operations.sharpen|integer|between:1,100',
            'save_as_new'              => 'boolean',
        ];
    }
}


<?php
// ============================================================
// FilterMediaRequest.php
// ============================================================
namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\MediaProcessingService;

class FilterMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $filters = implode(',', array_keys(MediaProcessingService::FILTERS));
        return [
            'filter'     => "required|string|in:{$filters}",
            'save_as_new' => 'boolean',
        ];
    }
}


<?php
// ============================================================
// TextOverlayRequest.php
// ============================================================
namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class TextOverlayRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'text'               => 'required|string|max:500',
            'options'            => 'nullable|array',
            'options.x'          => 'nullable|integer|min:0',
            'options.y'          => 'nullable|integer|min:0',
            'options.size'       => 'nullable|integer|between:8,300',
            'options.color'      => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'options.align'      => 'nullable|in:left,center,right',
            'options.valign'     => 'nullable|in:top,middle,bottom',
            'options.shadow'     => 'nullable|boolean',
            'options.shadow_color'  => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'options.shadow_offset' => 'nullable|integer|between:1,20',
        ];
    }
}


<?php
// ============================================================
// WatermarkRequest.php
// ============================================================
namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class WatermarkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'watermark' => 'nullable|file|mimes:png,webp|max:5120',
            'position'  => 'nullable|in:top-left,top-right,bottom-left,bottom-right,center',
            'opacity'   => 'nullable|integer|between:10,100',
            'padding'   => 'nullable|integer|between:0,100',
        ];
    }
}


<?php
// ============================================================
// CompressVideoRequest.php
// ============================================================
namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class CompressVideoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'quality'    => 'nullable|in:high,medium,low',
            'resolution' => 'nullable|in:1920x1080,1280x720,854x480,640x360',
            'format'     => 'nullable|in:mp4,webm',
            'fps'        => 'nullable|integer|in:24,30,60',
        ];
    }
}