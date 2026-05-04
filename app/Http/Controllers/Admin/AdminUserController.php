<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacebookPage;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Admin dashboard — list of all non-admin users and plans.
     */
public function index()
{
    $users = User::with('currentPlan')->where('is_admin', false)->get();
    $plans = Plan::all();

    return view('admin.dashboard', compact('users', 'plans'));
}

    /**
     * Create a new subscriber account, optionally with a linked Facebook page.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'plan_id'  => 'required|exists:plans,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'plan_id'  => $request->plan_id,
            'is_admin' => false,
        ]);

        if ($request->filled('page_id') && $request->filled('page_access_token')) {
            FacebookPage::create([
                'user_id'          => $user->id,
                'page_id'          => $request->page_id,
                'page_name'        => $request->page_name ?? 'Unnamed Page',
                'access_token'     => encrypt($request->page_access_token),
                'is_active'        => true,
                'token_expires_at' => now()->addDays(60),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success'   => true,
                'user'      => $user,
                'plan_name' => $user->currentPlan?->name ?? 'No plan',
            ]);
        }

        return back()->with('success', 'User created successfully.');
    }
}