<div id="payModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:10000;align-items:center;justify-content:center;padding:20px;"
     dir="ltr">
    <div style="background:#fff;border-radius:24px;box-shadow:0 32px 80px rgba(0,0,0,.3);max-width:500px;width:100%;overflow:hidden;animation:upModal-in .3s cubic-bezier(.34,1.56,.64,1);">

        <div style="height:5px;background:linear-gradient(90deg,#2563eb,#7c3aed,#ec4899);"></div>

        <div style="display:flex;gap:0;">

    
            <div style="background:#0f1117;padding:28px 24px;min-width:190px;display:flex;flex-direction:column;gap:8px;">
                <p style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;margin:0;">{{ __('Social Scheduler')}}</p>
                <p id="pay-plan-name" style="font-size:20px;font-weight:800;color:#fff;margin:0;font-family:'Syne',sans-serif;"></p>
                <p id="pay-plan-price-big" style="font-size:32px;font-weight:800;color:#fff;margin:0;"></p>
                <p style="font-size:12px;color:#6b7280;margin:0;">{{ __('per month')}}</p>
                <div style="border-top:1px solid #1f2937;margin:12px 0;"></div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:12px;color:#9ca3af;" id="pay-summary-label"></span>
                    <span style="font-size:12px;color:#9ca3af;" id="pay-summary-price"></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:12px;color:#9ca3af;">{{ __('Subtotal')}}</span>
                    <span style="font-size:12px;color:#9ca3af;" id="pay-subtotal"></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:12px;color:#9ca3af;">{{ __('Tax')}}</span>
                    <span style="font-size:12px;color:#9ca3af;">$0.00</span>
                </div>
                <div style="border-top:1px solid #1f2937;margin:8px 0;"></div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:13px;font-weight:700;color:#fff;">{{ __('Total due today')}}</span>
                    <span style="font-size:13px;font-weight:700;color:#fff;" id="pay-total"></span>
                </div>
                <div style="margin-top:auto;padding-top:16px;display:flex;align-items:center;gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#6b7280"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                    <span style="font-size:11px;color:#6b7280;">{{ __('Powered by Stripe')}}</span>
                </div>
            </div>

            {{-- Right: Card Form --}}
            <div style="flex:1;padding:28px 24px;position:relative;">
                <button onclick="closePayModal()"
                        style="position:absolute;top:16px;right:16px;color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <p style="font-size:15px;font-weight:700;color:#111827;margin:0 0 20px;">{{ __('Pay with card')}}</p>

                <div style="margin-bottom:16px;">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">{{ __('Email')}}</label>
                    <input type="email" id="pay-email" value="{{ auth()->user()->email }}"
                           style="width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:13px;color:#111827;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                <div style="margin-bottom:0;">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">{{ __('Card information')}}</label>
                    <div style="border:1.5px solid #d1d5db;border-radius:10px;overflow:hidden;">
                        <div style="display:flex;align-items:center;padding:10px 14px;border-bottom:1px solid #e5e7eb;gap:8px;">
                            <input type="text" id="pay-card-number" placeholder="1234 1234 1234 1234" maxlength="19"
                                   oninput="formatCardNumber(this)"
                                   style="flex:1;border:none;outline:none;font-size:13px;color:#111827;">
                            <div style="display:flex;gap:4px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png" style="height:18px;object-fit:contain;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png" style="height:18px;object-fit:contain;">
                            </div>
                        </div>
                        <div style="display:flex;">
                            <input type="text" id="pay-expiry" placeholder="MM / YY" maxlength="7"
                                   oninput="formatExpiry(this)"
                                   style="flex:1;padding:10px 14px;border:none;border-right:1px solid #e5e7eb;outline:none;font-size:13px;color:#111827;">
                            <input type="text" id="pay-cvc" placeholder="CVC" maxlength="3"
                                   style="flex:1;padding:10px 14px;border:none;outline:none;font-size:13px;color:#111827;">
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px;margin-bottom:20px;">
                    <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">{{ __('Name on card')}}</label>
                    <input type="text" id="pay-name" placeholder="Full name"
                           style="width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:13px;color:#111827;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                <div id="pay-error" style="display:none;background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;margin-bottom:12px;"></div>

                <button id="pay-submit-btn" onclick="submitPayment()"
                        style="width:100%;padding:13px;background:#6772e5;color:#fff;font-size:14px;font-weight:700;border-radius:10px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s;"
                        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <span id="pay-btn-text">{{ __('Subscribe')}} — <span id="pay-btn-price"></span> / {{ __('month')}}</span>
                    <svg id="pay-spinner" style="display:none;animation:spin 1s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg>
                </button>

                <p style="text-align:center;font-size:11px;color:#9ca3af;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:4px;">
                    <svg width="12" height="12" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    {{ __('Secured by Stripe')}}
                </p>
            </div>
        </div>
    </div>
</div>