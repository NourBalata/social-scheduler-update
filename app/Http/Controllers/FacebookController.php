<?php

namespace App\Http\Controllers;

use App\Contracts\SocialMediaProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    protected $socialService;

    public function __construct(SocialMediaProvider $socialService)
    {
        $this->socialService = $socialService;
    }

    public function redirect()
    {
        return redirect()->away($this->socialService->getAuthUrl());
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')->with('error', 'not access!');
        }

        try {

            $tokenData = $this->socialService->getAccessToken($request->code);

        dd($tokenData);
            if (empty($tokenData['access_token'])) {
                return redirect()->route('dashboard')->with('error', ' Access Token.');
            }

            $shortToken = $tokenData['access_token'];

          
            $longRes = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => config('services.facebook.client_id'),
                'client_secret'     => config('services.facebook.client_secret'),
                'fb_exchange_token' => $shortToken,
            ]);

            $userToken = $longRes->successful() 
                ? ($longRes->json('access_token') ?? $shortToken) 
                : $shortToken;

      
            $account = Auth::user()->facebookAccounts()->updateOrCreate(
                ['facebook_id' => $tokenData['user_id'] ?? null],
                [
                    'name'             => 'Facebook User',
                    'access_token'     => $userToken,
                    'token_expires_at' => now()->addDays(60),
                ]
            );

            $pages = $this->socialService->getUserPages($userToken);

            if (empty($pages)) {
                return redirect()->route('dashboard')->with('warning', 'done but not found pages');
            }

            $user = Auth::user();
            $linkedCount = 0;


// dd($pages);

foreach ($pages as $pageData) {
    if (empty($pageData['id']) || empty($pageData['access_token'])) {
        continue;
    }

    \App\Models\FacebookPage::updateOrCreate(
        [
            'page_id' => (string) $pageData['id'],
            'user_id' => $user->id,
        ], 
        [
            'page_name'           => $pageData['name'],
            'facebook_account_id' => $account->id,
            'access_token'        => $pageData['access_token'], 
            'token_expires_at'    => now()->addDays(60),
            'is_active'           => true,
        ]
    );

    $linkedCount++;
}

            return redirect()->route('dashboard')->with('success', "done .");

        } catch (\Exception $e) {
            Log::error('Facebook callback error', [
                'user_id' => Auth::id(), 
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')->with('error', 'Error : ' . $e->getMessage());
        }
    }
}