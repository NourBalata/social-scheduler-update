<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'content' => 'required|string|max:5000',
            'scheduled_at' => 'required|date|after:now', // لازم يكون في المستقبل
        ];
    }
}