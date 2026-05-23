<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'facebook_page_id' => [
                'required',
                'exists:facebook_pages,id',
                \Illuminate\Validation\Rule::exists('facebook_pages', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id());
                }),
            ],
            'content' => [
                'required',
                'string',
                'max:63206',
            ],
            'scheduled_at' => [
                'nullable',
                'date',
            ],
            'media'            => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov|max:102400',
            'media_library_id' => 'nullable|exists:media,id',
        ];
    }

    public function messages(): array
    {
        return [
            'facebook_page_id.exists'   => 'The selected Facebook page is invalid or does not belong to your account.',
            'content.max'               => 'Post content cannot exceed 63,206 characters.',
        ];
    }
}