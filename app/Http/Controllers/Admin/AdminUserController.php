<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class AdminUserController extends Controller
{

    public function index()
{
    $user = auth()->user();

  
    if ($user->is_admin) {
       $users = User::with('plan')->where('is_admin', 0)->get();
        $plans = \App\Models\Plan::all();
        
        return view('admin.dashboard', compact('users', 'plans'));
    }
    else{
         $pages = auth()->user()->facebookPages;
         $posts = auth()->user()->posts()->get();
        //  $media = auth()->user()->mediaLibrary()->latest()->get();
    $events = $posts->map(function ($post) {
        return [
            'title' => Str::limit($post->content, 20),
            'start' => $post->scheduled_at->toIso8601String(),
            'extendedProps' => [
                'status' => $post->status,
                'page' => $post->page_name,
            ],
            'color' => $post->published ? '#10b981' : '#3b82f6', 
        ];
    });
    return view('subscriber.dashboard', compact('pages','events'));
    }

}
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required',
        'plan_id' => 'required|exists:plans,id',
        
    ]);

    $user = User::create([
    'name'             => $request->name,
    'email'            => $request->email,
    'password'         => Hash::make($request->password),
    'plan_id'          => $request->plan_id,
    'fb_user_id'       => $request->fb_user_id,
    'fb_access_token'  => $request->fb_access_token,
    'fb_client_id'     => $request->fb_client_id,
    'fb_client_secret' => $request->fb_client_secret,
    'is_admin'         => false,
]);
if ($request->filled('page_id') && $request->filled('page_access_token')) {
    \App\Models\FacebookPage::create([
        'user_id'          => $user->id,
        'page_id'          => $request->page_id,
        'page_name'        => $request->page_name ?? 'page without name',
        'access_token'     => $request->page_access_token,
        'is_active'        => true,
        'token_expires_at' => now()->addDays(60),
    ]);
}
  
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'user' => $user,
            'plan_name' => $user->currentPlan?->name ?? 'no plan!'
        ]);
    }

    return back()->with('success', 'Added!!');
}

}