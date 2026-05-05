
@php
    $user     = auth()->user();
    $plan     = $user->currentPlan;
    $isActive = $user->hasActivePlan();
    $isFree   = $plan?->isFree() ?? true;
 
    // هل المودال يطلع تلقائياً؟
    $autoShow = false;
    $autoReason = '';
 
    if (!$isActive && !$isFree) {
        $autoShow   = true;
        $autoReason = 'expired';
    } elseif ($user->remainingPostsCount() === 0 && !$isFree && $isActive) {
        $autoShow   = true;
        $autoReason = 'limit';
    } elseif ($isFree && session('show_upgrade')) {
        $autoShow   = true;
        $autoReason = 'free_limit';
    }
 
    // fetch paid plans لعرضهم في المودال
    $paidPlans = \App\Models\Plan::where('active', true)
        ->where('price', '>', 0)
        ->orderBy('price')
        ->get();
@endphp
 
{{-- ────────────── Upgrade Modal ────────────── --}}
<div id="upgradeModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:20px;"
     dir="ltr">
 
    <div style="background:#fff;border-radius:24px;box-shadow:0 32px 80px rgba(0,0,0,.25);max-width:520px;width:100%;overflow:hidden;animation:upModal-in .3s cubic-bezier(.34,1.56,.64,1);">
 
        {{-- Top gradient bar --}}
        <div style="height:5px;background:linear-gradient(90deg,#2563eb,#7c3aed,#ec4899);"></div>
 
        {{-- Header --}}
        <div style="padding:24px 28px 0;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:44px;height:44px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="22" height="22" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:#0f1117;margin:0;" id="upModal-title">
                            Upgrade Your Plan
                        </h2>
                        <p style="font-size:13px;color:#9ca3af;margin:4px 0 0;" id="upModal-subtitle">
                            Unlock full access to all features
                        </p>
                    </div>
                </div>
                <button onclick="closeUpgradeModal()"
                        style="color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:4px;"
                        id="upModal-closeBtn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
 
            {{-- Reason banner --}}
            <div id="upModal-banner" style="border-radius:10px;padding:10px 14px;margin:12px 0 0;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;"></div>
        </div>
 
        {{-- Plans --}}
        <div style="padding:20px 28px;">
            <div style="display:flex;flex-direction:column;gap:10px;" id="upModal-plans">
                @foreach($paidPlans as $p)
                    @php
                        $isPopular  = $p->slug === 'pro';
                        $isCurrent  = $user->currentPlan?->id === $p->id && $isActive;
                    @endphp
                    <div style="border:2px solid {{ $isPopular ? '#2563eb' : '#e5e7eb' }};border-radius:14px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;position:relative;{{ $isPopular ? 'background:#f0f7ff;' : '' }}">
 
                        @if($isPopular)
                            <span style="position:absolute;top:-10px;left:16px;background:#2563eb;color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;">MOST POPULAR</span>
                        @endif
 
                        <div>
                            <p style="font-size:15px;font-weight:800;color:#111827;margin:0;font-family:'Syne',sans-serif;">{{ $p->name }}</p>
                            <p style="font-size:12px;color:#6b7280;margin:3px 0 0;">
                                {{ number_format($p->posts_limit) }} posts · {{ $p->pages_limit }} {{ $p->pages_limit > 1 ? 'pages' : 'page' }}
                            </p>
                        </div>
 
                        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                            <div style="text-align:right;">
                                <p style="font-size:20px;font-weight:800;color:#111827;margin:0;">${{ number_format($p->price, 0) }}</p>
                                <p style="font-size:11px;color:#9ca3af;margin:0;">/month</p>
                            </div>
 
                            @if($isCurrent)
                                <span style="padding:8px 16px;background:#e0e7ff;color:#3730a3;font-size:12px;font-weight:700;border-radius:10px;">Current</span>
                            @else
                                <form method="POST" action="{{ route('billing.checkout', $p) }}">
                                    @csrf
                                    <button type="submit"
                                            style="padding:9px 18px;background:{{ $isPopular ? 'linear-gradient(135deg,#2563eb,#7c3aed)' : '#111827' }};color:#fff;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;white-space:nowrap;transition:opacity .15s;"
                                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                                        {{ $user->hasActiveStripeSubscription() ? 'Switch' : 'Get Started' }} →
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
 
        {{-- Footer --}}
        <div style="padding:0 28px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <p style="font-size:12px;color:#9ca3af;display:flex;align-items:center;gap:5px;margin:0;">
                <svg width="13" height="13" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Secured by Stripe · Cancel anytime
            </p>
            <a href="{{ route('plans.index') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;">
                Compare all plans →
            </a>
        </div>
    </div>
</div>
 
{{-- ────────────── JS ────────────── --}}
<script>
(function () {
    /* ── helpers ── */
    function openUpgradeModal(reason) {
        const modal    = document.getElementById('upgradeModal');
        const title    = document.getElementById('upModal-title');
        const subtitle = document.getElementById('upModal-subtitle');
        const banner   = document.getElementById('upModal-banner');
        const closeBtn = document.getElementById('upModal-closeBtn');
 
        const configs = {
            expired: {
                title:    'Your Plan Has Expired',
                subtitle: 'Renew now to continue scheduling posts',
                banner:   '🔒 Your access has ended. Pick a plan below to get back up and running.',
                bannerBg: '#fee2e2', bannerColor: '#991b1b',
                canClose: true,
            },
            limit: {
                title:    "You've Hit Your Post Limit",
                subtitle: 'Upgrade to schedule more posts this month',
                banner:   '📊 You\'ve used all your posts for this month. Upgrade for more capacity.',
                bannerBg: '#fff3cd', bannerColor: '#856404',
                canClose: true,
            },
            free_limit: {
                title:    'Unlock Full Power',
                subtitle: 'You need a paid plan to use this feature',
                banner:   '✨ This feature is available on paid plans.',
                bannerBg: '#eff6ff', bannerColor: '#1d4ed8',
                canClose: true,
            },
            manual: {
                title:    'Upgrade Your Plan',
                subtitle: 'Unlock full access to all features',
                banner:   '',
                bannerBg: '', bannerColor: '',
                canClose: true,
            },
        };
 
        const cfg = configs[reason] || configs.manual;
        title.textContent    = cfg.title;
        subtitle.textContent = cfg.subtitle;
 
        if (cfg.banner) {
            banner.textContent        = cfg.banner;
            banner.style.background   = cfg.bannerBg;
            banner.style.color        = cfg.bannerColor;
            banner.style.display      = 'flex';
        } else {
            banner.style.display = 'none';
        }
 
        // hide close button if plan is expired and forced
        closeBtn.style.display = (cfg.canClose || reason !== 'expired') ? 'flex' : 'none';
 
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
 
    function closeUpgradeModal() {
        document.getElementById('upgradeModal').style.display = 'none';
        document.body.style.overflow = '';
    }
 
    /* expose globally */
    window.openUpgradeModal  = openUpgradeModal;
    window.closeUpgradeModal = closeUpgradeModal;
 
    /* close on backdrop click */
    document.getElementById('upgradeModal').addEventListener('click', function (e) {
        if (e.target === this) closeUpgradeModal();
    });
 
    /* ── auto-show on load ── */
    @if($autoShow)
        document.addEventListener('DOMContentLoaded', function () {
            openUpgradeModal('{{ $autoReason }}');
        });
    @endif
 
    /* ── intercept protected buttons ── */
    // أي زر/رابط عنده data-upgrade="true" يفتح المودال بدل ما يكمل
    @if(!$isActive && !$isFree)
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-upgrade]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openUpgradeModal('expired');
            });
        });
    });
    @endif
 
})();
</script>
 
<style>
@keyframes upModal-in {
    from { opacity: 0; transform: scale(.92) translateY(16px); }
    to   { opacity: 1; transform: scale(1)  translateY(0);     }
}
</style>