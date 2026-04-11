<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AdminUserController extends Controller
{
    // public function index()
    // {
    //     $users = User::where('is_admin', false)->with('currentPlan')->get();
    //     $plans = Plan::where('active', true)->get();
        
    //     return view('admin.dashboard', compact('users', 'plans'));
    // }
    public function index()
{
    $user = auth()->user();

    // إذا كان أدمن: جلب كل البيانات
    if ($user->is_admin) {
        $users = \App\Models\User::where('is_admin', false)->get();
        $plans = \App\Models\Plan::all();
        
        return view('admin.dashboard', compact('users', 'plans'));
    }
    else{
           $pages = auth()->user()->facebookPages; 
    
    return view('subscriber.dashboard', compact('pages'));
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
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'plan_id' => $request->plan_id,
        'is_admin' => false,
    ]);

    // إذا كان الطلب Ajax بنرجع JSON
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'user' => $user,
            'plan_name' => $user->currentPlan?->name ?? 'بدون خطة'
        ]);
    }

    return back()->with('success', 'تمت الإضافة');
}
//     public function store(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email',
//             'password' => 'required|min:8',
//             'plan_id' => 'required|exists:plans,id',
//             'fb_client_id' => 'nullable|string',
//             'fb_client_secret' => 'nullable|string',
//         ]);

//         User::create([
//             'name' => $request->name,
//             'email' => $request->email,
//             'password' => Hash::make($request->password),
//        'plan_id' => $request->plan_id,
//             'fb_client_id' => $request->fb_client_id,
//             'fb_client_secret' => $request->fb_client_secret,
//             'is_admin' => false, // دايماً false لأنه مشترك
//         ]);
// // dd($request->plan_id);
// dd($request->all());
//         return back()->with('success', 'تم إضافة المشترك بنجاح!');
//     }
}