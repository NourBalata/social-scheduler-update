<div id="calModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:460px;width:100%;max-height:90vh;overflow-y:auto;">

   
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #f3f4f6;position:sticky;top:0;background:#fff;z-index:1;">
            <h3 style="font-family:'Syne',sans-serif;font-weight:800;color:#0f1117;">{{__('Post Details')}}</h3>
            <button onclick="closeCalModal()" style="color:#9ca3af;border:none;background:none;cursor:pointer;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span id="cal-badge" style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;"></span>
                <span id="cal-type-badge" style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;display:none;"></span>
                <span id="cal-time" style="font-size:13px;color:#9ca3af;"></span>
            </div>

     
            <div>
                <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">{{__('Page')}}</p>
                <p id="cal-page" style="font-size:14px;font-weight:600;color:#111827;"></p>
            </div>

           
            <div>
                <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">{{ __('Content')}}</p>
                <p id="cal-content" style="font-size:13px;color:#374151;line-height:1.6;background:#f9fafb;border-radius:10px;padding:12px;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></p>
            </div>

            <div id="cal-retry-section" style="display:none;">

          
                <div id="cal-retry-analyzing" style="text-align:center;padding:20px;color:#6b7280;font-size:12px;">
                    <svg style="animation:spin 1s linear infinite;width:24px;height:24px;display:inline-block;margin-bottom:8px;" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
                    </svg>
                    <p style="margin:0;">{{ __('AI is analyzing the error...')}}</p>
                </div>

        
                <div id="cal-retry-result" style="display:none;">

             
                    <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
                        <p style="font-size:10px;font-weight:700;color:#991b1b;margin:0 0 4px;text-transform:uppercase;">Error</p>
                        <p id="cal-error-text" style="font-size:11px;color:#dc2626;margin:0;word-break:break-word;line-height:1.5;"></p>
                    </div>

                 
                    <div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:10px;padding:14px;margin-bottom:12px;">
                        <p id="cal-diagnosis-title" style="font-size:13px;font-weight:800;color:#0369a1;margin:0 0 6px;"></p>
                        <p id="cal-diagnosis-msg" style="font-size:12px;color:#374151;margin:0 0 14px;line-height:1.6;"></p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button id="cal-fix-btn" style="flex:1;padding:10px 16px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;min-width:120px;"></button>
                            <button onclick="closeCalModal()" style="padding:10px 16px;background:#f3f4f6;color:#6b7280;border:none;border-radius:10px;font-size:12px;font-weight:600;cursor:pointer;">Cancel</button>
                        </div>
                    </div>

                  
                    <div id="cal-token-form" style="display:none;background:#fffbeb;border:1.5px solid #fcd34d;border-radius:10px;padding:14px;">
                        <p style="font-size:12px;font-weight:700;color:#92400e;margin:0 0 4px;">🔑 Update Page Token Directly</p>
                        <p style="font-size:11px;color:#b45309;margin:0 0 10px;line-height:1.5;">
                            Rest New Page Access Token
                          
                        </p>

                        <input
                            type="text"
                            id="cal-update-token"
                            placeholder="Paste new Page Access Token..."
                            style="width:100%;padding:9px 12px;border:1.5px solid #fcd34d;border-radius:8px;font-size:12px;outline:none;box-sizing:border-box;margin-bottom:10px;"
                        >

                        <div style="display:flex;gap:8px;">
                            <button
                                id="cal-token-save-btn"
                                onclick="updatePageDetails()"
                                style="flex:1;padding:9px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;"
                            >✅ Save & Retry</button>

                            <button
                                onclick="window.location.href='/facebook/redirect'"
                                style="flex:1;padding:9px;background:#4f46e5;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;"
                            >🔗 Reconnect Facebook</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>