

<div id="templatesModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:560px;width:100%;max-height:80vh;display:flex;flex-direction:column;">
        <div style="padding:20px 24px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <h3 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#0f1117;">💾 My Templates</h3>
            <button onclick="closeTemplates()" style="color:#9ca3af;border:none;background:none;cursor:pointer;"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div style="flex:1;overflow-y:auto;padding:20px 24px;" id="templatesList">
            <div style="text-align:center;color:#9ca3af;font-size:13px;padding:32px;">Loading...</div>
        </div>
    </div>
</div>

