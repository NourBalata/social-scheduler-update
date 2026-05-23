<div class="section-title" style="display:flex;align-items:center;justify-content:space-between;">
    <span>{{ __('Plans')}}</span>
    <button class="add-btn" onclick="openPlanModal()" style="font-size:12px;padding:6px 13px;">+ {{ __('New Plan')}}</button>
</div>

<div class="plans-grid" style="margin-bottom:8px;">
    @foreach($plans as $plan)
    <div class="plan-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <div class="plan-name">{{ __('plans.' . $plan->slug) }}</div>
            @if($plan->active)
                <span class="badge badge-green" style="font-size:10px;">{{ __('Active')}}</span>
            @else
                <span class="badge badge-gray" style="font-size:10px;">{{ __('Inactive')}}</span>
            @endif
        </div>
        <div class="plan-price">${{ number_format($plan->price, 2) }}<span style="font-size:13px;font-weight:400;color:#6b7280;">/{{ __('mo')}}</span></div>
        <div class="plan-meta">{{ $plan->posts_limit }} {{ __('posts')}} · {{ $plan->pages_limit }} {{ __('pages')}}</div>
        @if($plan->stripe_price_id)
            <div class="plan-meta" style="margin-top:3px;font-size:11px;color:#9ca3af;">{{ $plan->stripe_price_id }}</div>
        @endif
        <div class="plan-actions">
            <button class="plan-edit-btn" onclick="editPlan({{ $plan->id }}, '{{ __('plans.' . $plan->slug) }}', {{ $plan->price }}, {{ $plan->posts_limit }}, {{ $plan->pages_limit }}, '{{ $plan->stripe_price_id }}', {{ $plan->active ? 'true' : 'false' }})">{{ __('Edit')}}</button>
            <span style="font-size:12px;color:#6b7280;padding:5px 0;">{{ $plansBreakdown->firstWhere('name', $plan->name)['count'] ?? 0 }} {{ __('users')}}</span>
        </div>
    </div>
    @endforeach
</div>