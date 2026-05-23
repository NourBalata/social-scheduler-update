<script>
let apGeneratedPosts = [];
let apSelectedTone   = 'friendly';

window.openAutopilotModal  = function() { document.getElementById('autopilotModal').classList.replace('hidden','flex'); showApStep(1); };
window.closeAutopilotModal = function() { document.getElementById('autopilotModal').classList.replace('flex','hidden'); };
document.getElementById('autopilotModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeAutopilotModal(); });

function showApStep(n) {
    [1,2,3,4].forEach(i => {
        const el = document.getElementById('apStep'+i);
        el.style.display = i === n ? (n === 1 || n === 3 ? 'flex' : 'block') : 'none';
    });
    if (n === 1) document.getElementById('apStep1').style.flexDirection = 'column';
    if (n === 3) document.getElementById('apStep3').style.flexDirection = 'column';
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
        errEl.textContent = 'Please fill in all required fields.'; errEl.style.display = 'block'; return;
    }
    errEl.style.display = 'none';
    showApStep(2);

    let prog = 0;
    const bar  = document.getElementById('ap-progress-bar');
    const msgs = ['Analyzing your business...','Writing educational content...','Adding promotional posts...','Arranging dates and times...','Reviewing the final plan...'];
    let msgIdx = 0;
    const interval = setInterval(() => {
        prog = Math.min(prog + (prog < 80 ? 2 : 0.3), 92);
        bar.style.width = prog + '%';
        if (msgIdx < msgs.length && prog > msgIdx * 18)
            document.getElementById('ap-loading-msg').textContent = msgs[msgIdx++];
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
            document.getElementById('ap-error').textContent    = data.error ?? 'Generation failed, please try again.';
            document.getElementById('ap-error').style.display  = 'block'; return;
        }
        apGeneratedPosts = data.posts;
        renderApPreview(data.posts, page);
        showApStep(3);
    } catch(e) {
        clearInterval(interval); showApStep(1);
        document.getElementById('ap-error').textContent   = 'A connection error occurred.';
        document.getElementById('ap-error').style.display = 'block';
    }
}

function renderApPreview(posts, pageName) {
    const typeEmoji   = { educational:'📚', promotional:'🛍️', entertainment:'🎉', engagement:'💬' };
    const typeColors2 = { educational:'#8b5cf6', promotional:'#f59e0b', entertainment:'#ec4899', engagement:'#06b6d4' };
    document.getElementById('ap-summary').innerHTML = `✅ Generated <strong>${posts.length}</strong> posts for page <strong>${pageName}</strong> — review the content before scheduling.`;
    const container = document.getElementById('ap-posts-preview');
    container.innerHTML = '';
    posts.forEach(p => {
        const color = typeColors2[p.post_type] || '#6b7280';
        const emoji = typeEmoji[p.post_type]   || '📝';
        const div   = document.createElement('div');
        div.style.cssText = 'background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;';
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
    const page = document.getElementById('ap-page').value;
    const btn  = document.getElementById('ap-confirm-btn');
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
        btn.disabled  = false;
        btn.innerHTML = 'Schedule All Now';
        showToast('❌ Save failed, please try again.');
    }
}

window.showApStep        = showApStep;
window.selectTone        = selectTone;
window.startAutopilot    = startAutopilot;
window.confirmAutopilot  = confirmAutopilot;
window.openDateClickModal  = openDateClickModal;
window.closeDateClickModal = closeDateClickModal;
window.selectDcType        = selectDcType;
window.generateDcPost      = generateDcPost;
window.regenerateDcPost    = regenerateDcPost;
window.saveDcPost          = saveDcPost;
let dcSelectedDate = '';
let dcSelectedType = 'educational';

function openDateClickModal(dateStr) {
    dcSelectedDate = dateStr;
    const label = new Date(dateStr).toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
    document.getElementById('dc-date-label').textContent        = label;
    document.getElementById('dc-scheduled-at').value            = dateStr + 'T18:00';
    document.getElementById('dcFormArea').style.display         = 'block';
    document.getElementById('dcResultArea').style.display       = 'none';
    document.getElementById('dc-error').style.display           = 'none';
    document.getElementById('dateClickModal').classList.replace('hidden','flex');
}
function closeDateClickModal() {
    document.getElementById('dateClickModal').classList.replace('flex','hidden');
}
document.getElementById('dateClickModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeDateClickModal(); });

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
        errEl.textContent = 'Please fill in all required fields.'; errEl.style.display = 'block'; return;
    }
    errEl.style.display = 'none';

    const btn  = document.getElementById('dc-gen-btn');
    const spin = document.getElementById('dc-spin');
    btn.disabled = true; spin.style.display = 'inline-block';

    try {
        const res  = await fetch("{{ route('autopilot.generate.single') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ date:dcSelectedDate, post_type:dcSelectedType, business_name:business, industry, audience, tone }),
        });
        const data = await res.json();
        if (!res.ok || data.error) {
            errEl.textContent = data.error ?? 'Generation failed.'; errEl.style.display = 'block'; return;
        }
        document.getElementById('dc-content').value = data.content;
        updateDcCharCount(document.getElementById('dc-content'));
        document.getElementById('dc-scheduled-at').value  = dcSelectedDate + 'T' + (data.suggested_time ?? '18:00');
        document.getElementById('dcResultArea').style.display = 'block';
    } catch(e) {
        errEl.textContent = 'A connection error occurred.'; errEl.style.display = 'block';
    } finally {
        btn.disabled = false; spin.style.display = 'none';
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
        errEl.textContent = 'Please fill in all required fields.'; errEl.style.display = 'block'; return;
    }
    errEl.style.display = 'none';
    const btn  = document.getElementById('dc-save-btn');
    const spin = document.getElementById('dc-save-spin');
    btn.disabled = true; spin.style.display = 'inline-block';
    try {
        const res  = await fetch("{{ route('autopilot.confirm.single') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ page_name:page, content, scheduled_at:scheduledAt, post_type:dcSelectedType }),
        });
        const data = await res.json();
        if (!res.ok || data.error) {
            errEl.textContent = data.error ?? 'Save failed.'; errEl.style.display = 'block'; return;
        }
        if (calendarInstance && data.event) calendarInstance.addEvent(data.event);
        showToast('✅ Post scheduled successfully!');
        closeDateClickModal();
    } catch(e) {
        errEl.textContent = 'A connection error occurred.'; errEl.style.display = 'block';
    } finally {
        btn.disabled = false; spin.style.display = 'none';
    }
}


</script>