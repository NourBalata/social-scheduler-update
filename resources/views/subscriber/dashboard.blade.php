<x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<x-slot name="header">
    <div style="display:flex;align-items:center;justify-content:space-between;" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <span class="brand" style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,#2563eb,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
    ⚡ {{ __('PostFlow') }}
        </span>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="user-chip">
                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    {{ __('Logout')}}
                </button>
            </form>
            <div style="display:flex;gap:8px;">
    <form method="POST" action="{{ route('lang.switch', 'ar') }}">
        @csrf
        <button type="submit" style="padding:6px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:{{ app()->getLocale() === 'ar' ? '#2563eb' : '#fff' }};color:{{ app()->getLocale() === 'ar' ? '#fff' : '#374151' }};font-weight:700;cursor:pointer;">
          {{ __('Arabic')}}
        </button>
    </form>
    <form method="POST" action="{{ route('lang.switch', 'en') }}">
        @csrf
        <button type="submit" style="padding:6px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:{{ app()->getLocale() === 'en' ? '#2563eb' : '#fff' }};color:{{ app()->getLocale() === 'en' ? '#fff' : '#374151' }};font-weight:700;cursor:pointer;">
            English
        </button>
    </form>

       <button onclick="openModal('publishedPostsModal')" class="add-btn" style="background:#f0fdf4;color:#065f46;border:1px solid #bbf7d0;">
    📋 {{ __('Published Posts') }}
</button>
</div>
        </div>
    </div>
</x-slot>

@php
    $user          = auth()->user();
    $plan          = $user->currentPlan;
    $isFree        = $plan?->isFree() ?? true;
    $isActive      = $user->hasActivePlan();
    $isPastDue     = $user->stripeSubscriptionIsPastDue();
    $isExpired     = !$isActive && !$isFree;

    $daysLeft      = null;
    $expiringSoon  = false;
    if ($user->plan_expires_at) {
        $daysLeft     = (int) now()->diffInDays($user->plan_expires_at, false);
        $expiringSoon = $daysLeft >= 0 && $daysLeft <= 5;
    }

    $postsLimit    = $plan?->posts_limit ?? 0;
    $postsUsed     = $postsLimit > 0 ? $postsLimit - $user->remainingPostsCount() : 0;
    $postsPercent  = $postsLimit > 0 ? min(100, round($postsUsed / $postsLimit * 100)) : 0;
    $postsBarColor = $postsPercent >= 90 ? '#ef4444' : ($postsPercent >= 70 ? '#f59e0b' : '#10b981');

    $statusColor = match(true) {
        $isPastDue    => ['bg'=>'#fff3cd','text'=>'#856404','dot'=>'#f59e0b'],
        $isExpired    => ['bg'=>'#fee2e2','text'=>'#991b1b','dot'=>'#ef4444'],
        $expiringSoon => ['bg'=>'#fff3cd','text'=>'#856404','dot'=>'#f59e0b'],
        $isActive     => ['bg'=>'#d1fae5','text'=>'#065f46','dot'=>'#10b981'],
        default       => ['bg'=>'#f3f4f6','text'=>'#6b7280','dot'=>'#9ca3af'],
    };
    $statusLabel = match(true) {
        $isPastDue    => 'Payment Failed',
        $isExpired    => 'Expired',
        $expiringSoon => "Expires in {$daysLeft}d",
        $isFree       => 'Free Plan',
        $isActive     => 'Active',
        default       => 'Inactive',
    };

    $autoShowUpgrade   = false;
    $autoUpgradeReason = '';
    if ($isExpired && !$isPastDue) {
        $autoShowUpgrade   = true;
        $autoUpgradeReason = 'expired';
    } elseif ($user->remainingPostsCount() === 0 && !$isFree && $isActive) {
        $autoShowUpgrade   = true;
        $autoUpgradeReason = 'limit';
    } elseif ($isFree && session('show_upgrade')) {
        $autoShowUpgrade   = true;
        $autoUpgradeReason = 'free_limit';
    }

    $paidPlans = \App\Models\Plan::where('active', true)->where('price', '>', 0)->orderBy('price')->get();
@endphp

<div style="position:relative;min-height:100vh;">
    <div class="dash-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="dash-wrap" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

        @include('subscriber.partials.flash')

        @if($expiredPages->isNotEmpty())
        <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
            <span style="font-size:22px;">🔴</span>
            <div>
                <p style="font-size:14px;font-weight:700;color:#991b1b;margin:0;">
                    Facebook token expired for:
                    <strong>{{ $expiredPages->pluck('page_name')->join(', ') }}</strong>
                </p>
                <p style="font-size:12px;color:#b91c1c;margin:4px 0 0;">
                    Posts will fail until you reconnect.
                    <a href="{{ route('facebook.redirect') }}" style="color:#991b1b;font-weight:700;text-decoration:underline;">Reconnect now →</a>
                </p>
            </div>
        </div>
        @endif

     

        @include('subscriber.partials.stats')
        @include('subscriber.partials.subscription')

        <div class="main-grid">
            @include('subscriber.partials.sidebar')
            <div style="display:flex;flex-direction:column;gap:20px;">
                @include('subscriber.partials.autopilot-banner')
                @include('subscriber.partials.create-post')
                @include('subscriber.partials.bulk-csv')
                @include('subscriber.partials.modals.salla')
            </div>
        </div>

        @include('subscriber.partials.calendar')

    </div>
</div>

@include('subscriber.partials.modals.upgrade')
@include('subscriber.partials.modals.payment')
@include('subscriber.partials.modals.page')
@include('subscriber.partials.modals.calendar')
@include('subscriber.partials.modals.media-library')
@include('subscriber.partials.modals.templates')

<div id="vue-app">
    @include('subscriber.partials.modals.autopilot')
    @include('subscriber.partials.modals.dateclick')
    @include('subscriber.partials.published-posts-modal')
        @include('subscriber.partials.reposts')
</div>

<div id="toast" style="position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(20px);background:#1f2937;color:#fff;padding:12px 22px;border-radius:99px;font-size:13px;font-weight:600;pointer-events:none;opacity:0;transition:all .3s;z-index:99999;white-space:nowrap;box-shadow:0 8px 24px rgba(0,0,0,.2);"></div>

@include('subscriber.partials.scripts.main')
@include('subscriber.partials.scripts.calendar')
@include('subscriber.partials.scripts.autopilot')
@include('subscriber.partials.scripts.media')
@include('subscriber.partials.styles')

</x-app-layout>

