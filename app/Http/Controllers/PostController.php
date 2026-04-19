<?php

namespace App\Http\Controllers;

use App\Models\ScheduledPost;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PostController extends Controller
{
    public function store(Request $request)
    {
        //   dd($request->all()); 
        $request->validate([
            'page_name'    => 'required|string',
            'content'      => 'required|string',
            'scheduled_at' => 'nullable|date', 
        ]);

        $page = auth()->user()->pages()
            ->where('page_name', 'LIKE', '%' . trim($request->page_name) . '%')
            ->first();

        if (!$page) {
            return back()
                ->withInput()
                ->withErrors(['page_name' => 'not found page']);
        }

        $post = ScheduledPost::create([
            'user_id'          => auth()->id(),
            'facebook_page_id' => $page->id,
            'content'          => $request->content,
            'scheduled_at'     => $request->scheduled_at ?? now(),
            'status'           => 'pending',
        ]);

        return back()->with('success', "done.");
    }

public function storeAnotherPage(Request $request)
{
    $request->validate([
        'page_id'           => 'required|string',
        'page_name'         => 'required|string',
        'page_access_token' => 'required|string',
    ]);
    auth()->user()->facebookPages()->create([
        'page_id'          => $request->page_id,
        'page_name'        => $request->page_name,
        'access_token'     => $request->page_access_token,
        'is_active'        => true,
        'token_expires_at' => now()->addDays(60),
    ]);

    return back()->with('success', 'done.');
}
}
