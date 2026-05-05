let currentPlanId = null;

function openPayModal(planName, planPrice, planId) {
    currentPlanId = planId;
    
    // حط البيانات
    document.getElementById('pay-plan-name').textContent  = planName + ' Plan';
    document.getElementById('pay-plan-price-big').textContent = '$' + parseFloat(planPrice).toFixed(2);
    document.getElementById('pay-summary-label').textContent  = planName + ' Plan — monthly';
    document.getElementById('pay-summary-price').textContent  = '$' + parseFloat(planPrice).toFixed(2);
    document.getElementById('pay-subtotal').textContent       = '$' + parseFloat(planPrice).toFixed(2);
    document.getElementById('pay-total').textContent          = '$' + parseFloat(planPrice).toFixed(2);
    document.getElementById('pay-btn-price').textContent      = '$' + parseFloat(planPrice).toFixed(2);

  
    closeUpgradeModal();
    document.getElementById('pay-error').style.display = 'none';
    document.getElementById('pay-card-number').value = '';
    document.getElementById('pay-expiry').value = '';
    document.getElementById('pay-cvc').value = '';
    document.getElementById('pay-name').value = '';
    document.getElementById('payModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePayModal() {
    document.getElementById('payModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) closePayModal();
});

function formatCardNumber(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = val.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 4);
    if (val.length >= 2) val = val.substring(0,2) + ' / ' + val.substring(2);
    input.value = val;
}

async function submitPayment() {
    const cardNumber = document.getElementById('pay-card-number').value.replace(/\s/g,'');
    const expiry     = document.getElementById('pay-expiry').value;
    const cvc        = document.getElementById('pay-cvc').value;
    const name       = document.getElementById('pay-name').value.trim();
    const email      = document.getElementById('pay-email').value.trim();
    const errEl      = document.getElementById('pay-error');

    if (!email || !cardNumber || !expiry || !cvc || !name) {
        errEl.textContent = 'Please fill in all fields.';
        errEl.style.display = 'block';
        return;
    }
    if (cardNumber.length < 16) {
        errEl.textContent = 'Please enter a valid card number.';
        errEl.style.display = 'block';
        return;
    }
    if (cvc.length < 3) {
        errEl.textContent = 'Please enter a valid CVC.';
        errEl.style.display = 'block';
        return;
    }

    errEl.style.display = 'none';

    const btn     = document.getElementById('pay-submit-btn');
    const btnText = document.getElementById('pay-btn-text');
    const spinner = document.getElementById('pay-spinner');
    btn.disabled          = true;
    btnText.style.display = 'none';
    spinner.style.display = 'inline-block';

    try {
        const res = await fetch(`/billing/fake-checkout/${currentPlanId}`, {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                'Accept':        'application/json',
            },
            credentials: 'same-origin',
        });

        const data = await res.json();

        if (data.success) {
            closePayModal();
            showToast('✅ Plan activated! Refreshing...');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message ?? 'Failed');
        }

    } catch(e) {
        btn.disabled          = false;
        btnText.style.display = 'inline';
        spinner.style.display = 'none';
        errEl.textContent     = e.message || 'Something went wrong.';
        errEl.style.display   = 'block';
    }
}

const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Page Modal
const pageModal = document.getElementById('pageModal');
document.getElementById('openPageModalBtnQuick')?.addEventListener('click', () => pageModal.classList.replace('hidden','flex'));
document.getElementById('closePageModalBtn')?.addEventListener('click',   () => pageModal.classList.replace('flex','hidden'));
document.getElementById('cancelPageModalBtn')?.addEventListener('click',  () => pageModal.classList.replace('flex','hidden'));

// Post Form Helpers
function setPostType(btn, type) {
    document.querySelectorAll('.post-type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('post_type_hidden').value = type;
    document.getElementById('mediaSection').style.display = type === 'text' ? 'none' : 'block';
}
function updateCharCount(el) {
    const len = el.value.length;
    const counter = document.getElementById('charCounter');
    counter.textContent = `${len} / 2200`;
    counter.className = 'char-counter' + (len > 2000 ? ' warn' : '') + (len > 2200 ? ' over' : '');
}
function appendHashtag(tag) {
    const ta = document.getElementById('post-content');
    ta.value += (ta.value && !ta.value.endsWith(' ') ? ' ' : '') + tag + ' ';
    updateCharCount(ta);
    ta.focus();
}
function fillBestTime() {
    const now = new Date();
    const pad = n => String(n).padStart(2,'0');
    document.getElementById('scheduled_at').value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T18:00`;
    showToast('⚡ Best time set: Today at 6:00 PM');
}
function saveDraft() {
    const content = document.getElementById('post-content').value;
    if (!content.trim()) return showToast('⚠️ Write something first!');
    localStorage.setItem('postflow_draft', content);
    showToast('✅ Draft saved locally');
}
function showToast(msg, duration = 2800) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), duration);
}
function removeHashtagFromContent(tag) {
    const ta = document.getElementById('post-content');
    ta.value = ta.value.replace(new RegExp('\\s?' + tag.replace('#','\\#') + '\\s?','g'),' ').trim();
    updateCharCount(ta);
}

// Magic Write
const aiBtn = document.getElementById('ai-magic-btn');
const contentTextarea = document.getElementById('post-content');
const aiLoader = document.getElementById('ai-loader');
aiBtn?.addEventListener('click', async () => {
    const text = contentTextarea.value.trim();
    if (text.length < 5) { showToast('💡 Type a quick idea first!'); return; }
    aiBtn.disabled = true; aiLoader.style.display = 'flex';
    try {
        const res  = await fetch("{{ route('ai.caption') }}", { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, credentials:'same-origin', body:JSON.stringify({idea:text}) });
        const data = await res.json();
        if (res.status === 429) { showToast('⏳ ' + (data.error ?? 'AI busy'), 4000); return; }
        if (data.captions?.length) showCaptionPicker(data.captions);
        if (data.hashtags?.length) {
            const container = document.getElementById('hashtagSuggestions');
            container.innerHTML = '';
            data.hashtags.forEach(tag => {
                const chip = document.createElement('span');
                chip.className = 'ai-suggestion-chip'; chip.textContent = tag;
                chip.onclick = () => {
                    if (chip.classList.contains('selected-hashtag')) { chip.classList.remove('selected-hashtag'); chip.style.background=''; chip.style.color=''; removeHashtagFromContent(tag); }
                    else { chip.classList.add('selected-hashtag'); chip.style.background='#4f46e5'; chip.style.color='#fff'; appendHashtag(tag); }
                };
                container.appendChild(chip);
            });
        }
    } catch(e) { showToast('❌ AI connection failed'); }
    finally { aiBtn.disabled = false; aiLoader.style.display = 'none'; }
});
function showCaptionPicker(captions) {
    document.getElementById('captionPicker')?.remove();
    const picker = document.createElement('div');
    picker.id = 'captionPicker';
    picker.style.cssText = 'background:#f8faff;border:1.5px solid #bfdbfe;border-radius:14px;padding:14px;margin-bottom:12px;display:flex;flex-direction:column;gap:8px;';
    const title = document.createElement('p');
    title.style.cssText = 'font-size:11px;font-weight:700;color:#4f46e5;margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;';
    title.textContent = '✨ Choose a caption:'; picker.appendChild(title);
    captions.forEach((caption,i) => {
        const btn = document.createElement('button'); btn.type='button';
        btn.style.cssText = 'text-align:left;padding:10px 14px;border-radius:10px;border:1.5px solid #e0e7ff;background:#fff;font-size:13px;color:#374151;cursor:pointer;line-height:1.5;';
        btn.innerHTML = `<span style="font-size:10px;font-weight:800;color:#7c3aed;margin-right:6px;">Option ${i+1}</span>${caption}`;
        btn.onclick = () => {
            contentTextarea.value=''; let j=0;
            const timer = setInterval(() => { if(j<caption.length){contentTextarea.value+=caption[j++];updateCharCount(contentTextarea);}else clearInterval(timer); },14);
            picker.remove();
        };
        picker.appendChild(btn);
    });
    contentTextarea.parentElement.parentElement.insertBefore(picker, contentTextarea.parentElement);
}

// Calendar
const typeColors = { educational:'#8b5cf6', promotional:'#f59e0b', entertainment:'#ec4899', engagement:'#06b6d4', manual:'#3b82f6' };

const events = @json($events);
document.getElementById('stat-scheduled').textContent = events.filter(e => e.extendedProps?.status === 'pending').length;
document.getElementById('stat-published').textContent = events.filter(e => e.extendedProps?.status === 'published').length;

let calendarInstance = null;
document.addEventListener('DOMContentLoaded', () => {
    calendarInstance = new FullCalendar.Calendar(document.getElementById('fc-calendar'), {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,listMonth' },
        buttonText: { today:'Today', month:'Month', listMonth:'List' },
        events: events.map(e => ({
            ...e,
            color: e.extendedProps?.status === 'published' ? '#10b981'
                 : e.extendedProps?.status === 'failed'    ? '#ef4444'
                 : typeColors[e.extendedProps?.post_type]  || '#3b82f6',
        })),
        eventClick(info) {
            const p      = info.event.extendedProps;
            const status = p.status ?? 'pending';
            const colorMap = { published:'background:#d1fae5;color:#065f46', failed:'background:#fee2e2;color:#991b1b', pending:'background:#dbeafe;color:#1e40af' };
            const badge = document.getElementById('cal-badge');
            badge.style.cssText = `font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;${colorMap[status]??colorMap.pending}`;
            badge.textContent = status;
            const typeBadge = document.getElementById('cal-type-badge');
            if (p.post_type && p.post_type !== 'manual') {
                typeBadge.style.cssText = `font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;background:${typeColors[p.post_type]}20;color:${typeColors[p.post_type]};display:inline-block;`;
                typeBadge.textContent = p.post_type;
            } else { typeBadge.style.display = 'none'; }
            document.getElementById('cal-time').textContent    = info.event.start?.toLocaleString('en-US',{dateStyle:'medium',timeStyle:'short'}) ?? '';
            document.getElementById('cal-page').textContent    = p.page ?? '—';
            document.getElementById('cal-content').textContent = p.content ?? '';
            document.getElementById('calModal').classList.replace('hidden','flex');
        },
        dateClick(info) {
            openDateClickModal(info.dateStr);
        },
        eventDisplay: 'block',
        dayMaxEvents: 3,
    });
    calendarInstance.render();
});
function closeCalModal() { document.getElementById('calModal').classList.replace('flex','hidden'); }
document.getElementById('calModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeCalModal(); });

// Autopilot Modal
let apGeneratedPosts = [];
let apSelectedTone   = 'friendly';

function openAutopilotModal()  { document.getElementById('autopilotModal').classList.replace('hidden','flex'); showApStep(1); }
function closeAutopilotModal() { document.getElementById('autopilotModal').classList.replace('flex','hidden'); }
document.getElementById('autopilotModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeAutopilotModal(); });

function showApStep(n) {
    [1,2,3,4].forEach(i => document.getElementById('apStep'+i).style.display = i===n ? (n===1||n===3?'flex':'block') : 'none');
    if (n===1) { document.getElementById('apStep1').style.flexDirection='column'; }
    if (n===3) { document.getElementById('apStep3').style.flexDirection='column'; }
}

function selectTone(btn) {
    document.querySelectorAll('#ap-tone-grid .ap-tone-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    apSelectedTone = btn.dataset.tone;
}

async function startAutopilot() {
    const business = document.getElementById('ap-business').value.trim();
    const industry = document.getElementById('ap-industry').value.trim();
    const audience = document.getElementById('ap-audience').value.trim();
    const goal     = document.getElementById('ap-goal').value.trim();
    const page     = document.getElementById('ap-page').value;
    const ppw      = document.getElementById('ap-ppw').value;
    const errEl    = document.getElementById('ap-error');

    if (!business || !industry || !audience || !goal || !page) {
        errEl.textContent = 'Please fill in all required fields.';
        errEl.style.display = 'block'; return;
    }
    errEl.style.display = 'none';
    showApStep(2);

    let prog = 0;
    const bar = document.getElementById('ap-progress-bar');
    const msgs = ['Analyzing your business...', 'Writing educational content...', 'Adding promotional posts...', 'Arranging dates and times...', 'Reviewing the final plan...'];
    let msgIdx = 0;
    const interval = setInterval(() => {
        prog = Math.min(prog + (prog < 80 ? 2 : 0.3), 92);
        bar.style.width = prog + '%';
        if (msgIdx < msgs.length && prog > msgIdx * 18) {
            document.getElementById('ap-loading-msg').textContent = msgs[msgIdx++];
        }
    }, 300);

    try {
        const res  = await fetch("{{ route('autopilot.generate') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ business_name:business, industry, audience, goal, page_name:page, posts_per_week:parseInt(ppw), tone:apSelectedTone }),
        });
        const data = await res.json();
        clearInterval(interval); bar.style.width = '100%';

        if (!res.ok || data.error) {
            showApStep(1);
            document.getElementById('ap-error').textContent = data.error ?? 'Generation failed, please try again.';
            document.getElementById('ap-error').style.display = 'block'; return;
        }

        apGeneratedPosts = data.posts;
        renderApPreview(data.posts, page);
        showApStep(3);

    } catch(e) {
        clearInterval(interval);
        showApStep(1);
        document.getElementById('ap-error').textContent = 'A connection error occurred.';
        document.getElementById('ap-error').style.display = 'block';
    }
}

function renderApPreview(posts, pageName) {
    const typeEmoji  = { educational:'📚', promotional:'🛍️', entertainment:'🎉', engagement:'💬' };
    const typeColors2 = { educational:'#8b5cf6', promotional:'#f59e0b', entertainment:'#ec4899', engagement:'#06b6d4' };
    document.getElementById('ap-summary').innerHTML = `✅ Generated <strong>${posts.length}</strong> posts for page <strong>${pageName}</strong> — review the content before scheduling.`;
    const container = document.getElementById('ap-posts-preview');
    container.innerHTML = '';
    posts.forEach((p, i) => {
        const color = typeColors2[p.post_type] || '#6b7280';
        const emoji = typeEmoji[p.post_type]   || '📝';
        const div = document.createElement('div');
        div.style.cssText = `background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;`;
        div.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;background:${color}18;color:${color};">${emoji} ${p.post_type}</span>
                <span style="font-size:11px;color:#9ca3af;">${p.scheduled_at?.split(' ')[0] ?? ''} ${p.scheduled_at?.split(' ')[1]?.slice(0,5) ?? ''}</span>
            </div>
            <p style="font-size:12px;color:#374151;line-height:1.5;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${p.content}</p>
        `;
        container.appendChild(div);
    });
}

async function confirmAutopilot() {
    const page    = document.getElementById('ap-page').value;
    const btn     = document.getElementById('ap-confirm-btn');
    btn.disabled  = true;
    btn.innerHTML = '<svg style="animation:spin 1s linear infinite" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg> Scheduling...';

    try {
        const res  = await fetch("{{ route('autopilot.confirm') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ page_name:page, posts:apGeneratedPosts }),
        });
        const data = await res.json();
        document.getElementById('ap-success-msg').textContent = data.message ?? 'Scheduled successfully! 🎉';
        showApStep(4);
        if (calendarInstance && apGeneratedPosts.length) {
            apGeneratedPosts.forEach(p => {
                calendarInstance.addEvent({
                    title: p.content.slice(0,25) + '...',
                    start: p.scheduled_at,
                    color: typeColors[p.post_type] || '#3b82f6',
                    extendedProps: { status:'pending', page, content:p.content, post_type:p.post_type },
                });
            });
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = 'Schedule All Now';
        showToast('❌ Save failed, please try again.');
    }
}

// Date Click Modal
let dcSelectedDate = '';
let dcSelectedType = 'educational';

function openDateClickModal(dateStr) {
    dcSelectedDate = dateStr;
    const label = new Date(dateStr).toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    document.getElementById('dc-date-label').textContent = label;
    document.getElementById('dc-scheduled-at').value = dateStr + 'T18:00';
    document.getElementById('dcFormArea').style.display  = 'block';
    document.getElementById('dcResultArea').style.display = 'none';
    document.getElementById('dc-error').style.display     = 'none';
    document.getElementById('dateClickModal').classList.replace('hidden','flex');
}
function closeDateClickModal() { document.getElementById('dateClickModal').classList.replace('flex','hidden'); }
document.getElementById('dateClickModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeDateClickModal(); });

function selectDcType(btn) {
    document.querySelectorAll('#dc-type-grid .ap-tone-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    dcSelectedType = btn.dataset.type;
}

async function generateDcPost() {
    const business = document.getElementById('dc-business').value.trim();
    const industry = document.getElementById('dc-industry').value.trim();
    const audience = document.getElementById('dc-audience').value.trim();
    const tone     = document.getElementById('dc-tone').value;
    const errEl    = document.getElementById('dc-error');

    if (!business || !industry || !audience) {
        errEl.textContent = 'Please fill in all required fields.'; errEl.style.display='block'; return;
    }
    errEl.style.display = 'none';

    const btn  = document.getElementById('dc-gen-btn');
    const spin = document.getElementById('dc-spin');
    btn.disabled=true; spin.style.display='inline-block';
    btn.childNodes[btn.childNodes.length-1].textContent = ' Generating...';

    try {
        const res  = await fetch("{{ route('autopilot.generate.single') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ date:dcSelectedDate, post_type:dcSelectedType, business_name:business, industry, audience, tone }),
        });
        const data = await res.json();

        if (!res.ok || data.error) {
            errEl.textContent = data.error ?? 'Generation failed.'; errEl.style.display='block'; return;
        }

        document.getElementById('dc-content').value = data.content;
        updateDcCharCount(document.getElementById('dc-content'));
        const suggestedAt = dcSelectedDate + 'T' + (data.suggested_time ?? '18:00');
        document.getElementById('dc-scheduled-at').value = suggestedAt;
        document.getElementById('dcResultArea').style.display = 'block';

    } catch(e) {
        errEl.textContent = 'A connection error occurred.'; errEl.style.display='block';
    } finally {
        btn.disabled=false; spin.style.display='none';
        btn.childNodes[btn.childNodes.length-1].textContent = ' ✨ Generate Post';
    }
}

function regenerateDcPost() {
    document.getElementById('dcResultArea').style.display = 'none';
    generateDcPost();
}

function updateDcCharCount(el) {
    document.getElementById('dc-char-count').textContent = el.value.length + ' / 2200';
}

async function saveDcPost() {
    const content     = document.getElementById('dc-content').value.trim();
    const page        = document.getElementById('dc-page').value;
    const scheduledAt = document.getElementById('dc-scheduled-at').value;
    const errEl       = document.getElementById('dc-save-error');

    if (!content || !page || !scheduledAt) {
        errEl.textContent='Please fill in all required fields.'; errEl.style.display='block'; return;
    }
    errEl.style.display='none';

    const btn  = document.getElementById('dc-save-btn');
    const spin = document.getElementById('dc-save-spin');
    btn.disabled=true; spin.style.display='inline-block';

    try {
        const res  = await fetch("{{ route('autopilot.confirm.single') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ page_name:page, content, scheduled_at:scheduledAt, post_type:dcSelectedType }),
        });
        const data = await res.json();

        if (!res.ok || data.error) {
            errEl.textContent = data.error ?? 'Save failed.'; errEl.style.display='block'; return;
        }

        if (calendarInstance && data.event) {
            calendarInstance.addEvent(data.event);
        }
        showToast('✅ Post scheduled successfully!');
        closeDateClickModal();

    } catch(e) {
        errEl.textContent='A connection error occurred.'; errEl.style.display='block';
    } finally {
        btn.disabled=false; spin.style.display='none';
    }
}

// Media Library
const ML = { page:1, type:'', search:'', selected:null, loading:false, timer:null };
async function mlFetch(url, options={}) {
    return fetch(url, { ...options, credentials:'same-origin', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json',...(options.headers||{})} });
}
function openMediaLibrary() { document.getElementById('mediaLibraryModal').classList.add('open'); document.body.style.overflow='hidden'; mlReset(); mlLoadMedia(); }
function closeMediaLibrary() { document.getElementById('mediaLibraryModal').classList.remove('open'); document.body.style.overflow=''; }
document.getElementById('mediaLibraryModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeMediaLibrary(); });
function mlSetTab(btn,tab) {
    document.querySelectorAll('#mediaLibraryModal .post-type-tab').forEach(b=>b.classList.remove('active')); btn.classList.add('active');
    const isUpload=tab==='upload';
    document.getElementById('mlGridView').style.display=isUpload?'none':'block';
    document.getElementById('mlUploadView').style.display=isUpload?'block':'none';
    document.getElementById('mlFooter').style.display=isUpload?'none':'flex';
    if(!isUpload){ML.type=tab==='all'?'':tab;mlReset();mlLoadMedia();}
}
function mlDebouncedSearch(v){clearTimeout(ML.timer);ML.timer=setTimeout(()=>{ML.search=v;mlReset();mlLoadMedia();},350);}
function mlReset(){ML.page=1;ML.selected=null;document.getElementById('mlGrid').innerHTML='';document.getElementById('mlEmpty').style.display='none';document.getElementById('mlLoadMoreWrapper').style.display='none';mlUpdateFooter();}
async function mlLoadMedia(append=false){
    if(ML.loading)return;ML.loading=true;
    const grid=document.getElementById('mlGrid');
    if(!append)grid.innerHTML=Array(12).fill('<div class="ml-skeleton"></div>').join('');
    try{
        const params=new URLSearchParams({page:ML.page,per_page:24,...(ML.type&&{type:ML.type}),...(ML.search&&{search:ML.search})});
        const res=await mlFetch(`/media?${params}`);
        if(res.status===401){grid.innerHTML='<div style="grid-column:1/-1;text-align:center;padding:32px;color:#ef4444;font-size:13px;">⚠️ Session expired.</div>';ML.loading=false;return;}
        if(!res.ok)throw new Error(`HTTP ${res.status}`);
        const data=await res.json();const files=data.data||[];const meta=data.meta||{};
        if(!append)grid.innerHTML='';
        document.getElementById('mlTotalCount').textContent=`${meta.total??files.length} files`;
        if(!files.length&&!append){document.getElementById('mlEmpty').style.display='block';}
        else files.forEach(f=>grid.appendChild(mlCard(f)));
        document.getElementById('mlLoadMoreWrapper').style.display=(meta.current_page??1)<(meta.last_page??1)?'block':'none';
    }catch(e){if(!append)grid.innerHTML='<div style="grid-column:1/-1;text-align:center;padding:32px;color:#9ca3af;font-size:13px;">Failed to load media.</div>';console.error(e);}
    finally{ML.loading=false;}
}
function mlCard(file){
    const div=document.createElement('div');div.className='ml-item';
    const isVideo=file.type==='video';const thumb=file.thumbnail_url||file.url;
    div.innerHTML=`<div class="ml-check"><svg width="10" height="10" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>${isVideo?`<video src="${file.url}" poster="${thumb}" muted playsinline preload="none"></video>`:`<img src="${thumb}" alt="${file.name??''}" loading="lazy">`}<div style="position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,.5);color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;text-transform:uppercase;">${file.type}</div>`;
    div.addEventListener('click',()=>{document.querySelectorAll('.ml-item.selected').forEach(el=>el.classList.remove('selected'));if(ML.selected?.id===file.id){ML.selected=null;}else{div.classList.add('selected');ML.selected={id:file.id,url:file.url,name:file.name??file.filename??'media',type:file.type};}mlUpdateFooter();});
    return div;
}
function mlUpdateFooter(){const btn=document.getElementById('mlConfirmBtn');const info=document.getElementById('mlSelectedInfo');if(ML.selected){btn.disabled=false;btn.style.opacity='1';btn.style.cursor='pointer';info.textContent=`✓ ${ML.selected.name}`;info.style.color='#2563eb';}else{btn.disabled=true;btn.style.opacity='.4';btn.style.cursor='not-allowed';info.textContent='No file selected';info.style.color='#9ca3af';}}
function mlConfirmSelection(){if(!ML.selected)return;document.getElementById('mediaLibraryId').value=ML.selected.id;document.getElementById('media').value='';showPostMediaPreview(ML.selected.url,ML.selected.name,ML.selected.type);closeMediaLibrary();showToast('Media selected from library');}
function mlLoadMore(){ML.page++;mlLoadMedia(true);}
async function mlUploadFiles(files){
    const prog=document.getElementById('mlUploadProgress');prog.innerHTML='';prog.style.display='flex';
    for(const file of Array.from(files)){
        const key='f'+Math.random().toString(36).slice(2);const row=document.createElement('div');row.style.cssText='background:#f9fafb;border-radius:10px;padding:12px 14px;';
        row.innerHTML=`<div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span style="font-size:12px;font-weight:600;color:#374151;">${file.name}</span><span id="pct-${key}" style="font-size:12px;font-weight:700;color:#2563eb;">0%</span></div><div style="background:#e5e7eb;border-radius:99px;height:5px;overflow:hidden;"><div id="bar-${key}" style="height:100%;border-radius:99px;background:linear-gradient(90deg,#2563eb,#7c3aed);width:0%;transition:width .2s;"></div></div>`;
        prog.appendChild(row);
        try{await new Promise((resolve,reject)=>{const fd=new FormData();fd.append('file',file);const xhr=new XMLHttpRequest();xhr.upload.onprogress=e=>{if(e.lengthComputable){const p=Math.round(e.loaded/e.total*100);document.getElementById(`bar-${key}`).style.width=p+'%';document.getElementById(`pct-${key}`).textContent=p+'%';}};xhr.onload=()=>{if(xhr.status===201||xhr.status===200){document.getElementById(`bar-${key}`).style.background='#10b981';document.getElementById(`pct-${key}`).textContent='✓';document.getElementById(`pct-${key}`).style.color='#10b981';resolve();}else{let errMsg='Upload failed';try{errMsg=JSON.parse(xhr.responseText)?.message??errMsg;}catch{}document.getElementById(`pct-${key}`).textContent='✗ '+errMsg;document.getElementById(`pct-${key}`).style.color='#ef4444';reject(new Error(errMsg));}};xhr.onerror=()=>reject(new Error('Network error'));xhr.open('POST','/media/upload');xhr.withCredentials=true;xhr.setRequestHeader('X-CSRF-TOKEN',CSRF);xhr.setRequestHeader('Accept','application/json');xhr.send(fd);});}catch(e){console.error('Upload error:',e);}
    }
    setTimeout(()=>{const allTab=document.querySelector('[data-tab="all"]');if(allTab)allTab.click();mlReset();mlLoadMedia();},1200);
}
function mlDragOver(e){e.preventDefault();const zone=document.getElementById('mlDropZone');zone.style.borderColor='#2563eb';zone.style.background='#eff6ff';}
function mlDragLeave(){const zone=document.getElementById('mlDropZone');zone.style.borderColor='#d1d5db';zone.style.background='transparent';}
function mlDrop(e){e.preventDefault();mlDragLeave();mlUploadFiles(e.dataTransfer.files);}
function handleDirectUpload(input){if(!input.files?.[0])return;document.getElementById('mediaLibraryId').value='';const file=input.files[0];showPostMediaPreview(URL.createObjectURL(file),file.name,file.type.startsWith('video')?'video':'image');}
function showPostMediaPreview(url,name,type){document.getElementById('postMediaUploadArea').style.display='none';document.getElementById('postMediaPreview').style.display='block';const img=document.getElementById('postMediaPreviewImg');const vid=document.getElementById('postMediaPreviewVid');img.style.display='none';vid.style.display='none';if(type==='video'){vid.src=url;vid.style.display='block';}else{img.src=url;img.style.display='block';}document.getElementById('postMediaPreviewName').textContent=name;}
function clearPostMedia(){document.getElementById('media').value='';document.getElementById('mediaLibraryId').value='';document.getElementById('postMediaPreviewImg').src='';document.getElementById('postMediaPreviewVid').src='';document.getElementById('postMediaPreview').style.display='none';document.getElementById('postMediaUploadArea').style.display='flex';}
