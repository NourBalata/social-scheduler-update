<div style="background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:16px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <span style="font-size:20px;">🤖</span>
            <h3 style="font-family:'Syne',sans-serif;font-size:16px;font-weight:800;color:#fff;margin:0;">{{ __('AI Content Autopilot')}}</h3>
            <span style="background:#7c3aed;color:#e9d5ff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;">{{ __('NEW')}}</span>
        </div>
        <p style="font-size:12px;color:#a5b4fc;margin:0;">{{ __('Schedule a full month of content in one click.') }}</p>
    </div>

    @if($isActive)
        <button onclick="openAutopilotModal()" style="background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;font-weight:700;font-size:13px;padding:12px 22px;border-radius:12px;border:none;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            {{ __('Get Started')}}
        </button>
    @else
        <button onclick="openUpgradeModal('expired')" style="background:rgba(255,255,255,.15);color:#fff;font-weight:700;font-size:13px;padding:12px 22px;border-radius:12px;border:1.5px solid rgba(255,255,255,.3);cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            {{ __('Upgrade to Unlock')}}
        </button>
    @endif
</div>