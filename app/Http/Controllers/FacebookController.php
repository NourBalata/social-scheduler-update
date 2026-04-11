<?php

namespace App\Http\Controllers;

use App\Contracts\SocialMediaProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacebookController extends Controller
{
    protected $socialService;

    public function __construct(SocialMediaProvider $socialService)
    {
        $this->socialService = $socialService;
    }

    public function redirect()
    {
        // حقن البيانات ديناميكياً كما فعلتِ سابقاً
        config([
            'services.facebook.client_id' => session('fb_client_id'),
            'services.facebook.client_secret' => session('fb_client_secret'),
            'services.facebook.redirect' => url('/auth/facebook/callback'),
        ]);

        return redirect()->away($this->socialService->getAuthUrl());
    }

  public function callback(Request $request)
{
    if ($request->has('error')) {
        return redirect()->route('dashboard')->with('error', 'رفضت الدخول!');
    }

    
    $tokenData = $this->socialService->getAccessToken($request->code);
    $userToken = $tokenData['access_token'];

    $response = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v18.0/oauth/access_token", [
        'grant_type' => 'fb_exchange_token',
        'client_id' => config('services.facebook.client_id'),
        'client_secret' => config('services.facebook.client_secret'),
        'fb_exchange_token' => $userToken,
    ]);

    if ($response->successful()) {
        $userToken = $response->json()['access_token'] ?? $userToken;
    }

    
    $account = Auth::user()->facebookAccounts()->updateOrCreate(
        ['facebook_id' => $tokenData['user_id']],
        [
            'name' => 'Facebook User', 
            'access_token' => $userToken,
            'token_expires_at' => now()->addDays(60), // التوكن الطويل بضل شهرين
        ]
    );

   
    $pages = $this->socialService->getUserPages($userToken);

    foreach ($pages as $page) {
        if (Auth::user()->canAddPage()) {
            Auth::user()->pages()->updateOrCreate(
                ['page_id' => 'me'], 
                [
                    'facebook_account_id' => $account->id,
                    'page_name' => $page['name'],
                    'access_token' => $page['access_token'], // توكن الصفحة
                ]
            );
        }
    }

    return redirect()->route('dashboard')->with('success', 'تم ربط الحساب بنجاح وتفعيل توكن طويل الأمد!');
}
}