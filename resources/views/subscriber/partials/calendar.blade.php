<div class="calendar-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <h2 style="font-size:18px;font-weight:800;color:var(--ink);display:flex;align-items:center;gap:8px;">
            <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('Content Calendar')}}
        </h2>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:12px;font-weight:700;color:#6b7280;">
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#3b82f6;display:inline-block;"></span>{{ __('Scheduled')}}</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#10b981;display:inline-block;"></span>{{ __('Published')}}</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;"></span>{{ __('Failed')}}</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#8b5cf6;display:inline-block;"></span>{{ __('Educational')}}</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>{{ __('Promotional')}}</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#ec4899;display:inline-block;"></span>{{ __('Entertainment')}}</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#06b6d4;display:inline-block;"></span>{{ __('Engagement')}}</span>
        </div>
    </div>
    <div id="fc-calendar"></div>
</div>