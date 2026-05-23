<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private readonly StripeService $stripe
    ) {}

    public function index()
    {
        $plans = Plan::where('active', true)->orderBy('price')->get();
        $user  = auth()->user();

        return view('Plans.index', compact('plans', 'user'));
    }

  
    public function subscribe(Plan $plan)
    {
        return app(BillingController::class)->checkout($plan);
    }

   
    public function cancel()
    {
        return app(BillingController::class)->cancel();
    }
}