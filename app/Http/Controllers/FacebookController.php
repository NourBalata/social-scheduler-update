<?php

namespace App\Http\Controllers;

use App\Contracts\SocialMediaProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    try {
        $count = $this->socialService->syncAccount(Auth::user(), $request->code);
        return redirect()->route('dashboard')->with('success', "Linked $count pages.");
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return redirect()->route('dashboard')->with('error', "Failed to connect.");
    }
}
}