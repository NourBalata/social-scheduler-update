<div id="upgradeModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:20px;"
     dir="ltr">
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
                        <h2 id="upModal-title" style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:#0f1117;margin:0;">Upgrade Your Plan</h2>
                        <p id="upModal-subtitle" style="font-size:13px;color:#9ca3af;margin:4px 0 0;">Unlock full access to all features</p>
                    </div>
                </div>
                <button id="upModal-closeBtn" onclick="closeUpgradeModal()"
                        style="color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:4px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="upModal-banner" style="border-radius:10px;padding:10px 14px;margin:12px 0 0;font-size:13px;font-weight:600;display:none;align-items:center;gap:8px;"></div>
        </div>