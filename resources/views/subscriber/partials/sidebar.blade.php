<div class="sidebar-card">
    <div class="sidebar-section-title">{{ __('ACTIVE PAGES') }}</div>
    @forelse($user->facebookPages as $page)
        <div class="page-item">
            <span style="font-size:13px;font-weight:600;color:#111827;">{{ $page->page_name }}</span>
            <div class="page-dot"></div>
        </div>
    @empty
        <div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;font-style:italic;">{{ __('No connected pages')}}.</div>
    @endforelse

    <div class="best-times">
        <div class="best-times-title">⏰ {{ __('Best Times to Post')}}</div>
        <div class="time-slot"><span class="time-slot-label">9:00 AM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:88%"></div></div><span class="time-slot-pct">88%</span></div>
        <div class="time-slot"><span class="time-slot-label">12:00 PM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:74%"></div></div><span class="time-slot-pct">74%</span></div>
        <div class="time-slot"><span class="time-slot-label">6:00 PM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:95%"></div></div><span class="time-slot-pct">95%</span></div>
        <div class="time-slot"><span class="time-slot-label">9:00 PM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:61%"></div></div><span class="time-slot-pct">61%</span></div>
       
    </div>

    <div class="quick-actions">
        <div class="sidebar-section-title" style="padding:0 0 8px;">{{ __('Quick Actions')}}</div>
        <button class="quick-btn" onclick="document.getElementById('openPageModalBtnQuick').click()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
           {{ __('Add New Page')}}
        </button>
        <button class="quick-btn" onclick="openMediaLibrary()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('Media Library')}}
        </button>
        <button class="quick-btn" onclick="fillBestTime()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            {{ __('Use Best Time')}}
        </button>
    </div>
    <button class="quick-btn" onclick="openSallaModal()"
        style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);border-color:#ddd6fe;color:#5b21b6;font-weight:800;position:relative;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    Salla Products
    @if(!auth()->user()->sallaStore)
        <span style="position:absolute;top:-4px;right:-4px;background:#7c3aed;color:#fff;font-size:9px;font-weight:800;padding:2px 5px;border-radius:999px;line-height:1;">NEW</span>
    @else
        <span style="position:absolute;top:-4px;right:-4px;width:10px;height:10px;background:#10b981;border-radius:50%;border:2px solid #fff;"></span>
    @endif
</button>
</div>