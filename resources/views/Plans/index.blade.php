<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f5f5f5; padding: 40px 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 40px; color: #333; }
        .plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .plan { background: white; padding: 32px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; }
        .plan.active { border: 2px solid #4542ee; }
        .plan h2 { font-size: 24px; margin-bottom: 8px; }
        .plan .price { font-size: 36px; font-weight: bold; color: #333; margin: 16px 0; }
        .plan .price span { font-size: 18px; color: #666; }
        .plan ul { list-style: none; margin: 24px 0; }
        .plan li { padding: 8px 0; color: #666; }
        .plan li:before { content: "✓ "; color: #4542ee; font-weight: bold; }
        .plan button { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; background: #4542ee; color: white; font-weight: 500; }
        .plan button:hover { background: #4542ee; }
        .plan button:disabled { background: #ccc; cursor: not-allowed; }
        .badge { position: absolute; top: 16px; right: 16px; background: #4542ee; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .alert { padding: 16px; margin-bottom: 24px; border-radius: 8px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Choose Your Plan</h1>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <div class="plans">
            @foreach($plans as $plan)
                <div class="plan {{ $currentPlan && $currentPlan->id === $plan->id ? 'active' : '' }}">
                    @if($currentPlan && $currentPlan->id === $plan->id)
                        <span class="badge">Current Plan</span>
                    @endif

                    <h2>{{ $plan->name }}</h2>
                    <div class="price">
                        ${{ number_format($plan->price, 2) }}
                        <span>/month</span>
                    </div>

                    <ul>
                        <li>{{ $plan->posts_limit }} posts per month</li>
                        <li>{{ $plan->pages_limit }} Facebook {{ $plan->pages_limit > 1 ? 'pages' : 'page' }}</li>
                        <li>Post scheduling</li>
                        @if(!$plan->isFree())
                            <li>Priority support</li>
                        @endif
                    </ul>

                    @if($currentPlan && $currentPlan->id === $plan->id)
                        @if(!$plan->isFree())
                            <form method="POST" action="{{ route('plans.cancel') }}">
                                @csrf
                                <button type="submit">Cancel Subscription</button>
                            </form>
                        @else
                            <button disabled>Current Plan</button>
                        @endif
                    @else
                        <form method="POST" action="{{ route('plans.subscribe', $plan) }}">
                            @csrf
                            <button type="submit">
                                {{ $plan->isFree() ? 'Get Started' : 'Subscribe Now' }}
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>