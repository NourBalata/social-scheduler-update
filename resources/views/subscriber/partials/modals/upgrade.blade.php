<div id="upgradeModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:20px;"
     dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div style="background:#fff;border-radius:24px;box-shadow:0 32px 80px rgba(0,0,0,.25);max-width:520px;width:100%;overflow:hidden;animation:upModal-in .3s cubic-bezier(.34,1.56,.64,1);">

        <div style="height:5px;background:linear-gradient(90deg,#2563eb,#7c3aed,#ec4899);"></div>

        <div style="padding:24px 28px 0;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:44px;height:44px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="22" height="22" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="upModal-title" style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:#0f1117;margin:0;">{{ __('Upgrade Your Plan') }}</h2>
                        <p id="upModal-subtitle" style="font-size:13px;color:#9ca3af;margin:4px 0 0;">{{ __('Unlock full access to all features') }}</p>
                    </div>
                </div>
                <button id="upModal-closeBtn" onclick="closeUpgradeModal()"
                        style="color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:4px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="upModal-banner" style="border-radius:10px;padding:10px 14px;margin:12px 0 0;font-size:13px;font-weight:600;display:none;align-items:center;gap:8px;"></div>
        </div>

        <div style="padding:20px 28px;">
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($paidPlans as $p)
                    @php
                        $isPopular = $p->slug === 'pro';
                        $isCurrent = $user->currentPlan?->id === $p->id && $isActive;
                    @endphp
                    <div style="border:2px solid {{ $isPopular ? '#2563eb' : '#e5e7eb' }};border-radius:14px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;position:relative;{{ $isPopular ? 'background:#f0f7ff;' : '' }}">
                        @if($isPopular)
                            <span style="position:absolute;top:-10px;{{ app()->getLocale() === 'ar' ? 'right:16px;' : 'left:16px;' }}background:#2563eb;color:#fff;font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;">
                                {{ __('MOST POPULAR') }}
                            </span>
                        @endif
                        <div>
                            <p style="font-size:15px;font-weight:800;color:#111827;margin:0;font-family:'Syne',sans-serif;">{{ $p->name }}</p>
                            <p style="font-size:12px;color:#6b7280;margin:3px 0 0;">
                                {{ number_format($p->posts_limit) }} {{ __('posts') }} · {{ $p->pages_limit }} {{ $p->pages_limit > 1 ? __('pages') : __('page') }}
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                            <div style="text-align:{{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                                <p style="font-size:20px;font-weight:800;color:#111827;margin:0;">${{ number_format($p->price, 0) }}</p>
                                <p style="font-size:11px;color:#9ca3af;margin:0;">{{ __('/month') }}</p>
                            </div>
                            @if($isCurrent)
                                <span style="padding:8px 16px;background:#e0e7ff;color:#3730a3;font-size:12px;font-weight:700;border-radius:10px;">
                                    {{ __('Current') }}
                                </span>
                            @else
                                <button type="button"
                                    onclick="openPayModal('{{ $p->name }}', {{ $p->price }}, {{ $p->id }})"
                                    style="padding:9px 18px;background:{{ $isPopular ? 'linear-gradient(135deg,#2563eb,#7c3aed)' : '#111827' }};color:#fff;font-size:13px;font-weight:700;border-radius:10px;border:none;cursor:pointer;white-space:nowrap;transition:opacity .15s;"
                                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                                    {{ $user->hasActiveStripeSubscription() ? __('Switch') : __('Get Started') }} →
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="padding:0 28px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <p style="font-size:12px;color:#9ca3af;display:flex;align-items:center;gap:5px;margin:0;">
                <svg width="13" height="13" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                {{ __('Secured by Stripe · Cancel anytime') }}
            </p>
            <a href="{{ route('plans.index') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;">
                {{ __('Compare all plans') }} →
            </a>
        </div>

    </div>
</div>