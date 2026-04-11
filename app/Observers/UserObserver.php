<?php

namespace App\Observers;

use App\Models\User;
use App\Models\FacebookPage;

class UserObserver
{
    public function created(\App\Models\User $user)
{
   

    // إنشاء السجل الذي سيبحث عنه المستخدم باسمه لاحقاً
    $user->facebookPages()->create([
        'page_name' => "صفحة " . $user->name, 
        'page_id' => 'me', 
        'access_token' => null, // سيبقى نل حتى يتم الربط الحقيقي عبر فيسبوك
        'is_active' => false,
    ]);

}
}