<?php

namespace App\Services\Social;

use App\Models\FacebookPage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class FacebookAuthService
{
    /**
     * مزامنة صفحات فيسبوك الخاصة بالمستخدم
     */
    public function syncUserPages(string $userToken)
    {
        // 1. طلب قائمة الصفحات (Accounts) من Facebook Graph API
        $response = Http::get("https://graph.facebook.com/v19.0/me/accounts", [
            'access_token' => $userToken
        ]);

        if ($response->failed()) {
            // في حال فشل الطلب (مثلاً التوكن غلط أو منتهي)
            return false;
        }

        $pages = $response->json('data') ?? [];

        foreach ($pages as $page) {
            // 2. تخزين أو تحديث بيانات الصفحة
            // استخدمنا updateOrCreate عشان لو الصفحة موجودة نحدث الـ Token تبعها بس
            FacebookPage::updateOrCreate(
                ['page_id' => $page['id']],
                [
                    // إذا مش مسجل دخول، بنحط ID مؤقت أو 1
                    'user_id'      => Auth::id() ?? session('temp_user_id', 1),
                    'name'         => $page['name'],
                    // هاد هو الـ Page Access Token وهو ضروري جداً للنشر لاحقاً
                    'access_token' => $page['access_token'], 
                ]
            );
        }

        return true;
    }
}