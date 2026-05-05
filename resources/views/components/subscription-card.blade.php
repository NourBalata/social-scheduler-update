 
@php
    $user    = auth()->user();
    $plan    = $user->currentPlan;
    $isFree  = $plan?->isFree() ?? true;
    $isActive = $user->hasActivePlan();
 
    // احسب الأيام المتبقية للـ trial / manual expiry
    $daysLeft   = null;
    $expiringSoon = false;
    if ($user->plan_expires_at) {
        $daysLeft = (int) now()->diffInDays($user->plan_expires_at, false);
        $expiringSoon = $daysLeft >= 0 && $daysLeft <= 5;
    }
 
    $isPastDue  = $user->stripeSubscriptionIsPastDue();
    $isExpired  = !$isActive && !$isFree;
 
    // لون الـ badge حسب الحالة
    $statusColor = match(true) {
        $isPastDue   => ['bg' => '#fff3cd', 'text' => '#856404', 'dot' => '#f59e0b'],
        $isExpired   => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#ef4444'],
        $expiringSoon=> ['bg' => '#fff3cd', 'text' => '#856404', 'dot' => '#f59e0b'],
        $isActive    => ['bg' => '#d1fae5', 'text' => '#065f46', 'dot' => '#10b981'],
        default      => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'dot' => '#9ca3af'],
    };
 
    $statusLabel = match(true) {
        $isPastDue    => 'Payment Failed',
        $isExpired    => 'Expired',
        $expiringSoon => "Expires in {$daysLeft}d",
        $isFree       => 'Free Plan',
        $isActive     => 'Active',
        default       => 'Inactive',
    };
 
    // نسبة الـ posts المستهلكة
    $postsLimit = $plan?->posts_limit ?? 0;
    $postsUsed  = $postsLimit - $user->remainingPostsCount();
    $postsPercent = $postsLimit > 0 ? min(100, round($postsUsed / $postsLimit * 100)) : 0;
    $postsBarColor = $postsPercent >= 90 ? '#ef4444' : ($postsPercent >= 70 ? '#f59e0b' : '#10b981');
@endphp
 
{{-- ─────────────── Subscription Card ─────────────── --}}
<div class="sub-card" id="subscriptionCard">
 
    {{-- Header Row --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin:0;">Current Plan</p>
                <p style="font-size:16px;font-weight:800;color:#111827;margin:0;font-family:'Syne',sans-serif;">
                    {{ $plan?->name ?? 'No Plan' }}
                    @if(!$isFree && $plan)
                        <span style="font-size:13px;font-weight:600;color:#6b7280;">— ${{ number_format($plan->price, 0) }}/mo</span>
                    @endif
                </p>
            </div>
        </div>
 
        {{-- Status Badge --}}
        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:99px;font-size:12px;font-weight:700;background:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};">
            <span style="width:7px;height:7px;border-radius:50%;background:{{ $statusColor['dot'] }};{{ $isActive && !$isPastDue && !$expiringSoon ? 'animation:sub-pulse 2s infinite;' : '' }}"></span>
            {{ $statusLabel }}
        </span>
    </div>
 
    {{-- Past Due Warning --}}
    @if($isPastDue)
        <div style="background:#fff3cd;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
            <span style="font-size:16px;">⚠️</span>
            <div style="flex:1;">
                <p style="font-size:13px;font-weight:700;color:#92400e;margin:0;">Payment failed — update your card to stay active.</p>
            </div>
            <form method="POST" action="{{ route('billing.portal') }}">
                @csrf
                <button type="submit" style="background:#f59e0b;color:#fff;font-size:12px;font-weight:700;padding:6px 14px;border-radius:8px;border:none;cursor:pointer;">Fix Now →</button>
            </form>
        </div>
    @endif
 
    {{-- Expiring Soon Warning --}}
    @if($expiringSoon && !$isPastDue)
        <div style="background:#fff3cd;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
            <span style="font-size:16px;">⏳</span>
            <p style="font-size:13px;font-weight:700;color:#92400e;margin:0;flex:1;">
                Your plan expires in <strong>{{ $daysLeft }} {{ $daysLeft == 1 ? 'day' : 'days' }}</strong>. Upgrade to keep access.
            </p>
        </div>
    @endif
 
    {{-- Expired --}}
    @if($isExpired && !$isPastDue)
        <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:10px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
            <span style="font-size:16px;">🔒</span>
            <p style="font-size:13px;font-weight:700;color:#991b1b;margin:0;flex:1;">Your plan has expired. Upgrade to regain access.</p>
        </div>
    @endif
 
    {{-- Posts Usage Bar --}}
    @if($postsLimit > 0)
        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:12px;font-weight:600;color:#6b7280;">Posts this month</span>
                <span style="font-size:12px;font-weight:700;color:{{ $postsPercent >= 90 ? '#ef4444' : '#374151' }};">
                    {{ $postsUsed }} / {{ number_format($postsLimit) }}
                </span>
            </div>
            <div style="background:#f3f4f6;border-radius:99px;height:6px;overflow:hidden;">
                <div style="width:{{ $postsPercent }}%;height:100%;background:{{ $postsBarColor }};border-radius:99px;transition:width .4s;"></div>
            </div>
            @if($postsPercent >= 90)
                <p style="font-size:11px;color:#ef4444;font-weight:600;margin-top:4px;">⚠ Almost at limit — upgrade for more posts</p>
            @endif
        </div>
    @endif
 
    {{-- Pages usage --}}
    @if($plan && $plan->pages_limit > 0)
        @php $pagesUsed = auth()->user()->facebookPages->count(); @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f9fafb;border-radius:10px;margin-bottom:16px;">
            <span style="font-size:12px;font-weight:600;color:#6b7280;display:flex;align-items:center;gap:6px;">
                <svg width="13" height="13" fill="#10b981" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Connected Pages
            </span>
            <span style="font-size:13px;font-weight:700;color:#111827;">{{ $pagesUsed }} / {{ $plan->pages_limit }}</span>
        </div>
    @endif
 
    {{-- Action Buttons --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if($isFree || $isExpired || !$isActive)
            {{-- Upgrade CTA --}}
            <a href="{{ route('plans.index') }}"
               style="flex:1;min-width:140px;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 20px;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-weight:700;font-size:14px;border-radius:12px;text-decoration:none;transition:opacity .15s;"
               onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Upgrade Now
            </a>
        @else
            {{-- Manage Billing --}}
            @if($user->hasActiveStripeSubscription())
                <form method="POST" action="{{ route('billing.portal') }}" style="flex:1;min-width:140px;">
                    @csrf
                    <button type="submit" style="width:100%;padding:11px 20px;border:2px solid #2563eb;color:#2563eb;font-weight:700;font-size:14px;border-radius:12px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .15s;"
                        onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Manage Billing
                    </button>
                </form>
            @endif
 
            {{-- View Plans --}}
            <a href="{{ route('plans.index') }}"
               style="padding:11px 18px;border:1.5px solid #e5e7eb;color:#6b7280;font-weight:600;font-size:13px;border-radius:12px;text-decoration:none;display:flex;align-items:center;gap:6px;transition:all .15s;background:#fff;"
               onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                View Plans
            </a>
        @endif
 
        {{-- Invoices --}}
        @if($user->invoices()->count() > 0)
            <a href="{{ route('billing.invoices') }}"
               style="padding:11px 14px;border:1.5px solid #e5e7eb;color:#9ca3af;font-size:12px;font-weight:600;border-radius:12px;text-decoration:none;display:flex;align-items:center;gap:5px;transition:all .15s;background:#fff;"
               onmouseover="this.style.color='#374151';this.style.background='#f9fafb'" onmouseout="this.style.color='#9ca3af';this.style.background='#fff'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Invoices
            </a>
        @endif
    </div>
</div>
 
<style>
.sub-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1.5px solid #f0f0f0;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    margin-bottom: 20px;
}
@keyframes sub-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .6; transform: scale(1.3); }
}
</style>