<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacebookPage;
use App\Models\Plan;
use App\Models\ScheduledPost;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    
    public function index()
    {
        $users = User::with(['currentPlan', 'facebookPages', 'scheduledPosts'])
            ->where('is_admin', false)
            ->latest()
            ->get();

        $plans = Plan::orderBy('price')->get();

        $stats = [
            'total_users'       => $users->count(),
            'active_paid'       => $users->filter(fn($u) => $u->hasActiveStripeSubscription())->count(),
            'total_pages'       => FacebookPage::count(),
            'posts_this_month'  => ScheduledPost::whereYear('created_at', now()->year)
                                    ->whereMonth('created_at', now()->month)
                                    ->count(),
            'posts_published'   => ScheduledPost::where('status', 'published')->count(),
            'posts_failed'      => ScheduledPost::where('status', 'failed')->count(),

            'mrr'               => SubscriptionInvoice::where('status', 'paid')
                                    ->whereYear('paid_at', now()->year)
                                    ->whereMonth('paid_at', now()->month)
                                    ->sum('amount'),
            'total_revenue'     => SubscriptionInvoice::where('status', 'paid')->sum('amount'),
            'invoices_count'    => SubscriptionInvoice::where('status', 'paid')->count(),
        ];

        $revenueChart = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            return [
                'label'  => $date->format('M'),
                'amount' => SubscriptionInvoice::where('status', 'paid')
                    ->whereYear('paid_at', $date->year)
                    ->whereMonth('paid_at', $date->month)
                    ->sum('amount'),
            ];
        });

        $plansBreakdown = $plans->map(fn($plan) => [
            'name'  => $plan->name,
            'count' => $users->where('plan_id', $plan->id)->count(),
            'price' => $plan->price,
        ]);

        return view('admin.dashboard', compact(
            'users', 'plans', 'stats', 'revenueChart', 'plansBreakdown'
        ));
    }

  
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
                'user'      => $user->load('currentPlan'),
                'plan_name' => $user->currentPlan?->name ?? 'Free',
            ]);
        }

        return back()->with('success', 'User created successfully.');
    }

    public function updatePlan(Request $request, User $user)
    {
        $request->validate([
            'plan_id'         => 'required|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        $user->update([
            'plan_id'         => $plan->id,
            'plan_expires_at' => $request->plan_expires_at
                ? Carbon::parse($request->plan_expires_at)
                : ($plan->isFree() ? null : now()->addMonth()),
            // إذا الأدمن يغير يدوياً نمسح Stripe status
            'stripe_status'   => $plan->isFree() ? null : $user->stripe_status,
        ]);

        return response()->json([
            'success'   => true,
            'plan_name' => $plan->name,
        ]);
    }

    public function toggleActive(User $user)
    {
        // نستخدم plan_expires_at كـ soft disable:
        // إذا انتهت قبل الآن = disabled
        if ($user->hasActivePlan() && ! $user->currentPlan?->isFree()) {
            $user->update(['plan_expires_at' => now()->subDay()]);
            return response()->json(['status' => 'disabled']);
        }

        $user->update(['plan_expires_at' => now()->addMonth()]);
        return response()->json(['status' => 'enabled']);
    }


    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true]);
    }

  
    public function storePlan(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'slug'           => 'required|string|unique:plans,slug',
            'price'          => 'required|numeric|min:0',
            'posts_limit'    => 'required|integer|min:1',
            'pages_limit'    => 'required|integer|min:1',
            'stripe_price_id'=> 'nullable|string',
        ]);

        $plan = Plan::create([
            'name'            => $request->name,
            'slug'            => $request->slug,
            'price'           => $request->price,
            'posts_limit'     => $request->posts_limit,
            'pages_limit'     => $request->pages_limit,
            'stripe_price_id' => $request->stripe_price_id,
            'active'          => true,
        ]);

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function updatePlanDetails(Request $request, Plan $plan)
    {
        $request->validate([
            'price'           => 'required|numeric|min:0',
            'posts_limit'     => 'required|integer|min:1',
            'pages_limit'     => 'required|integer|min:1',
            'stripe_price_id' => 'nullable|string',
            'active'          => 'boolean',
        ]);

        $plan->update($request->only([
            'price', 'posts_limit', 'pages_limit', 'stripe_price_id', 'active',
        ]));

        return response()->json(['success' => true]);
    }
}