<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'      => 'required|file|max:102400|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv',
            'folder_id' => 'nullable|exists:media_folders,id',
            'tags'      => 'nullable|array',
        ];
    }
}