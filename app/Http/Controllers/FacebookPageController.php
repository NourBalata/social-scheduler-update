<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FacebookPageController extends Controller
{
    /**
     * Manually link an additional Facebook page to the authenticated user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'page_id'           => 'required|string',
            'page_name'         => 'required|string|max:255',
            'page_access_token' => 'required|string',
        ]);

        auth()->user()->facebookPages()->create([
            'page_id'          => $request->page_id,
            'page_name'        => $request->page_name,
            'access_token'     => encrypt($request->page_access_token),
            'is_active'        => true,
            'token_expires_at' => now()->addDays(60),
        ]);

        return back()->with('success', 'Page linked successfully.');
    }
}