<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
  public function index()
{
    // 1. جلب كل الخطط المفعلة
    $plans = Plan::where('active', true)->get();

    // 2. جلب المستخدم الأول (بما أننا في مرحلة التطوير وبدون لوجن)
    $user = \App\Models\User::first();

    // 3. تحديد الخطة الحالية: 
    // إذا وجد مستخدم، نأخذ خطته. 
    // إذا لم يوجد مستخدم أو لم يكن له خطة، نأخذ الخطة المجانية كقيمة افتراضية.
    $currentPlan = $user?->plan ?? Plan::where('slug', 'free')->first();

    return view('plans.index', compact('plans', 'currentPlan'));
}

    public function subscribe(Plan $plan)
    {
        $user = auth()->user();

        if ($plan->isFree()) {
            $user->update([
                'plan_id' => $plan->id,
                'plan_expires_at' => null
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Subscribed to Free plan');
        }

        // هون لازم يكون في payment gateway
        // بس هلا نحطها مؤقت
        $user->update([
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth()
        ]);

        return redirect()->route('dashboard')
            ->with('success', "Subscribed to {$plan->name} plan");
    }

    public function cancel()
    {
        $freePlan = Plan::where('slug', 'free')->first();
        
        auth()->user()->update([
            'plan_id' => $freePlan->id,
            'plan_expires_at' => null
        ]);

        return redirect()->route('plans.index')
            ->with('success', 'Subscription cancelled');
    }
}