<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Services\Social\FacebookAuthService;

class FacebookAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')
            ->scopes(['pages_manage_posts', 'pages_read_engagement', 'pages_show_list'])
            ->redirect();
    }

    public function callback(FacebookAuthService $authService)
    {
        try {
            // Socialite بيجيب المستخدم الأساسي (البروفايل)
            $user = Socialite::driver('facebook')->user();

            // هون بنبعت التوكن للسيرفس عشان تجيب الصفحات وتخزنهم
            $authService->syncUserPages($user->token);

            return redirect('/dashboard')->with('success', 'Pages linked successfully!');
        } catch (\Exception $e) {
            return redirect('/dashboard')->with('error', 'Failed to link Facebook.');
        }
    }
}