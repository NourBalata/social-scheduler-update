<div id="calModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:460px;width:100%;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #f3f4f6;">
            <h3 style="font-family:'Syne',sans-serif;font-weight:800;color:#0f1117;">Post Details</h3>
            <button onclick="closeCalModal()" style="color:#9ca3af;border:none;background:none;cursor:pointer;"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span id="cal-badge" style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;"></span>
                <span id="cal-type-badge" style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;display:none;"></span>
                <span id="cal-time" style="font-size:13px;color:#9ca3af;"></span>
            </div>
            <div>
                <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Page</p>
                <p id="cal-page" style="font-size:14px;font-weight:600;color:#111827;"></p>
            </div>
            <div>
                <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Content</p>
                <p id="cal-content" style="font-size:13px;color:#374151;line-height:1.6;background:#f9fafb;border-radius:10px;padding:12px;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></p>
            </div>
        </div>
    </div>
</div>