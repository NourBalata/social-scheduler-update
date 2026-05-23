
<div id="pageModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:480px;width:100%;">
        <div style="padding:20px 24px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#0f1117;">Add New Page</h3>
            <button id="closePageModalBtn" style="color:#9ca3af;border:none;background:none;cursor:pointer;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('facebook.pages.store') }}" method="POST" style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div><label class="dash-label">Page ID</label><input type="text" name="page_id" class="dash-input" placeholder="e.g. 123456789"></div>
            <div><label class="dash-label">Page Name</label><input type="text" name="page_name" class="dash-input" placeholder="My Business Page"></div>
            <div><label class="dash-label">Page Access Token</label><input type="text" name="page_access_token" class="dash-input" placeholder="EAAxxxxxx..."></div>
            <div style="display:flex;gap:10px;padding-top:8px;border-top:1px solid #f3f4f6;">
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Save Page</button>
                <button type="button" id="cancelPageModalBtn" style="padding:12px 20px;border:1.5px solid #e5e7eb;border-radius:12px;font-weight:600;font-size:14px;color:#6b7280;cursor:pointer;background:#fff;">Cancel</button>
            </div>
        </form>
    </div>
</div>
