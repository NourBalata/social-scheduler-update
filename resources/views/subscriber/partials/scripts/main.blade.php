<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';


function setPostType(btn, type) {
    document.querySelectorAll('.post-type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('post_type_hidden').value = type;
    const ms = document.getElementById('mediaSection');
    if (ms) ms.style.display = type === 'text' ? 'none' : 'block';
}


function updateCharCount(el) {
    const cnt = document.getElementById('charCounter');
    if (cnt) cnt.textContent = el.value.length + ' / 2200';
}


function fillBestTime() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('scheduled_at').value =
        `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T18:00`;
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
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), duration);
}

function removeHashtagFromContent(tag) {
    const ta = document.getElementById('post-content');
    ta.value = ta.value.replace(new RegExp('\\s?' + tag.replace('#','\\#') + '\\s?','g'),' ').trim();
    updateCharCount(ta);
    updatePreview();
}
function appendHashtag(tag) {
    const ta = document.getElementById('post-content');
    ta.value = (ta.value.trim() + ' ' + tag).trim();
    updateCharCount(ta);
    updatePreview();
}

const aiBtn           = document.getElementById('ai-magic-btn');
const contentTextarea = document.getElementById('post-content');
const aiLoader        = document.getElementById('ai-loader');

aiBtn?.addEventListener('click', async () => {
    const text = contentTextarea.value.trim();
    if (text.length < 5) { showToast('💡 Type a quick idea first!'); return; }
    aiBtn.disabled = true; aiLoader.style.display = 'flex';
    try {
        const res  = await fetch("{{ route('ai.caption') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            credentials: 'same-origin',
            body: JSON.stringify({ idea: text }),
        });
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
                    if (chip.classList.contains('selected-hashtag')) {
                        chip.classList.remove('selected-hashtag');
                        chip.style.background = ''; chip.style.color = '';
                        removeHashtagFromContent(tag);
                    } else {
                        chip.classList.add('selected-hashtag');
                        chip.style.background = '#4f46e5'; chip.style.color = '#fff';
                        appendHashtag(tag);
                    }
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
    title.textContent = '✨ Choose a caption:';
    picker.appendChild(title);
    captions.forEach((caption, i) => {
        const btn = document.createElement('button'); btn.type = 'button';
        btn.style.cssText = 'text-align:left;padding:10px 14px;border-radius:10px;border:1.5px solid #e0e7ff;background:#fff;font-size:13px;color:#374151;cursor:pointer;line-height:1.5;';
        btn.innerHTML = `<span style="font-size:10px;font-weight:800;color:#7c3aed;margin-right:6px;">Option ${i+1}</span>${caption}`;
        btn.onclick = () => {
            contentTextarea.value = ''; let j = 0;
            const timer = setInterval(() => {
                if (j < caption.length) {
                    contentTextarea.value += caption[j++];
                    updateCharCount(contentTextarea);
                    updatePreview();
                } else {
                    clearInterval(timer);
                }
            }, 14);
            picker.remove();
        };
        picker.appendChild(btn);
    });
    contentTextarea.parentElement.parentElement.insertBefore(picker, contentTextarea.parentElement);
}


function saveAsTemplate() {
    const content  = document.getElementById('post-content')?.value?.trim();
    const pageSelect = document.querySelector('select[name="facebook_page_id"]');
    const pageName = pageSelect?.options[pageSelect.selectedIndex]?.text?.trim();
    if (!content) { showToast('⚠️ Write some content first!'); return; }
    fetch('/templates', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        credentials: 'same-origin',
        body: JSON.stringify({
            name:    pageName ? `${pageName} — ${new Date().toLocaleDateString()}` : `Template ${new Date().toLocaleDateString()}`,
            content: content,
        }),
    })
    .then(res => res.json())
    .then(data => data.success ? showToast('💾 Template saved!') : showToast('❌ ' + (data.error ?? 'Failed.')))
    .catch(() => showToast('❌ Connection error.'));
}

function openTemplates() {
    document.getElementById('templatesModal').classList.replace('hidden','flex');
    loadTemplates();
}

function closeTemplates() {
    document.getElementById('templatesModal').classList.replace('flex','hidden');
}

function loadTemplates() {
    const list = document.getElementById('templatesList');
    list.innerHTML = '<div style="text-align:center;color:#9ca3af;font-size:13px;padding:32px;">Loading...</div>';
    fetch('/templates', { headers:{ 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' }, credentials:'same-origin' })
    .then(res => res.json())
    .then(data => {
        const templates = data.templates ?? [];
        if (!templates.length) {
            list.innerHTML = '<div style="text-align:center;color:#9ca3af;font-size:13px;padding:32px;">No templates yet. Save one first!</div>';
            return;
        }
        list.innerHTML = '';
        templates.forEach(t => {
            const card = document.createElement('div');
            card.style.cssText = 'background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:10px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;';
            card.dataset.templateId = t.id;
            card.innerHTML = `
                <div style="flex:1;cursor:pointer;" onclick="useTemplate(${t.id}, \`${t.content.replace(/`/g,"'")}\`)">
                    <p style="font-size:13px;font-weight:700;color:#111827;margin:0 0 4px;">${t.name}</p>
                    <p style="font-size:12px;color:#6b7280;margin:0;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${t.content}</p>
                </div>
                <button onclick="deleteTemplate(${t.id}, this)" style="flex-shrink:0;width:28px;height:28px;border:1px solid #fca5a5;border-radius:8px;background:#fff;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>`;
            list.appendChild(card);
        });
    })
    .catch(() => {
        list.innerHTML = '<div style="text-align:center;color:#ef4444;font-size:13px;padding:32px;">Failed to load templates.</div>';
    });
}

function useTemplate(id, content) {
    const ta = document.getElementById('post-content');
    ta.value = content;
    updateCharCount(ta);
    updatePreview();
    closeTemplates();
    showToast('✅ Template applied!');
}

function deleteTemplate(id, btn) {
    const card = btn.closest('[data-template-id]');
    card.style.opacity = '0.4';
    fetch(`/templates/${id}`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN':CSRF }, credentials:'same-origin' })
    .then(res => res.json())
    .then(data => {
        if (data.success) { card.remove(); showToast('🗑️ Template deleted.'); }
        else { card.style.opacity = '1'; showToast('❌ Failed to delete.'); }
    })
    .catch(() => { card.style.opacity = '1'; showToast('❌ Connection error.'); });
}


(function () {
    function openUpgradeModal(reason) {
        const modal    = document.getElementById('upgradeModal');
        const title    = document.getElementById('upModal-title');
        const subtitle = document.getElementById('upModal-subtitle');
        const banner   = document.getElementById('upModal-banner');
        const configs  = {
            expired:    { title:'Your Plan Has Expired', subtitle:'Renew now to continue scheduling posts', banner:'🔒 Your access has ended. Pick a plan below to get back up and running.', bannerBg:'#fee2e2', bannerColor:'#991b1b' },
            limit:      { title:"You've Hit Your Post Limit", subtitle:'Upgrade to schedule more posts this month', banner:"📊 You've used all your posts for this month. Upgrade for more.", bannerBg:'#fff3cd', bannerColor:'#856404' },
            free_limit: { title:'Unlock Full Power', subtitle:'You need a paid plan to use this feature', banner:'✨ This feature is available on paid plans.', bannerBg:'#eff6ff', bannerColor:'#1d4ed8' },
            manual:     { title:'Upgrade Your Plan', subtitle:'Unlock full access to all features', banner:'', bannerBg:'', bannerColor:'' },
        };
        const cfg = configs[reason] || configs.manual;
        title.textContent    = cfg.title;
        subtitle.textContent = cfg.subtitle;
        if (cfg.banner) {
            banner.textContent = cfg.banner; banner.style.background = cfg.bannerBg; banner.style.color = cfg.bannerColor; banner.style.display = 'flex';
        } else { banner.style.display = 'none'; }
        modal.style.display = 'flex'; document.body.style.overflow = 'hidden';
    }
    function closeUpgradeModal() {
        document.getElementById('upgradeModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    window.openUpgradeModal  = openUpgradeModal;
    window.closeUpgradeModal = closeUpgradeModal;
    document.getElementById('upgradeModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeUpgradeModal(); });

    @if($autoShowUpgrade)
        document.addEventListener('DOMContentLoaded', () => openUpgradeModal('{{ $autoUpgradeReason }}'));
    @endif
})();

let payPlanId = null;
function openPayModal(name, price, planId) {
    payPlanId = planId;
    document.getElementById('pay-plan-name').textContent      = name;
    document.getElementById('pay-plan-price-big').textContent = '$' + price;
    document.getElementById('pay-summary-label').textContent  = name + ' Plan';
    document.getElementById('pay-summary-price').textContent  = '$' + price + '/mo';
    document.getElementById('pay-subtotal').textContent       = '$' + price + '.00';
    document.getElementById('pay-total').textContent          = '$' + price + '.00';
    document.getElementById('pay-btn-price').textContent      = '$' + price;
    document.getElementById('payModal').style.display = 'flex';
    document.getElementById('pay-error').style.display = 'none';
}
function closePayModal() {
    document.getElementById('payModal').style.display = 'none';
}
document.getElementById('payModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closePayModal(); });

function formatCardNumber(input) {
    input.value = input.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim().slice(0,19);
}
function formatExpiry(input) {
    let v = input.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.slice(0,2) + ' / ' + v.slice(2);
    input.value = v.slice(0,7);
}
async function submitPayment() {
    if (!payPlanId) return;
    const errEl = document.getElementById('pay-error');
    const btn   = document.getElementById('pay-submit-btn');
    const spin  = document.getElementById('pay-spinner');
    btn.disabled = true; spin.style.display = 'inline-block';
    document.getElementById('pay-btn-text').style.display = 'none';
    try {
        const res  = await fetch(`/billing/fake-checkout/${payPlanId}`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (data.url) { window.location.href = data.url; return; }
        if (data.success) { window.location.reload(); return; }
        errEl.textContent = data.error ?? 'Payment failed, please try again.'; errEl.style.display = 'block';
    } catch(e) { errEl.textContent = 'Connection error.'; errEl.style.display = 'block'; }
    finally { btn.disabled = false; spin.style.display = 'none'; document.getElementById('pay-btn-text').style.display = 'inline'; }
}


function updatePreview() {
    const content = document.getElementById('post-content')?.value || '';
    const previewContent = document.getElementById('preview_content');
    if (previewContent)
        previewContent.textContent = content.trim() || 'Your post content will appear here...';

    const pageSelect = document.querySelector('select[name="facebook_page_id"]');
    const pageName = pageSelect?.options[pageSelect?.selectedIndex]?.text?.trim() || '';
    const previewPage = document.getElementById('preview_page_name');
    if (previewPage) previewPage.textContent = pageName || 'Your Page Name';

    const scheduledAt = document.getElementById('scheduled_at')?.value;
    const previewTime = document.getElementById('preview_time');
    if (previewTime) {
        if (scheduledAt) {
            const date = new Date(scheduledAt);
            previewTime.textContent = date.toLocaleDateString('en-US', {
                month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'
            });
        } else {
            previewTime.textContent = 'Just now';
        }
    }
}

function handleDirectUpload(input) {
    const file = input.files[0];
    if (!file) return;
    const previewDiv  = document.getElementById('postMediaPreview');
    const previewImg  = document.getElementById('postMediaPreviewImg');
    const previewVid  = document.getElementById('postMediaPreviewVid');
    const previewName = document.getElementById('postMediaPreviewName');
    const uploadArea  = document.getElementById('postMediaUploadArea');
    const url = URL.createObjectURL(file);
    if (file.type.startsWith('image/')) {
        previewImg.src = url; previewImg.style.display = 'block';
        previewVid.style.display = 'none';
    } else {
        previewVid.src = url; previewVid.style.display = 'block';
        previewImg.style.display = 'none';
    }
    if (previewName) previewName.textContent = file.name;
    if (previewDiv)  previewDiv.style.display  = 'block';
    if (uploadArea)  uploadArea.style.display  = 'none';
    const container = document.getElementById('preview_media_container');
    const pImg      = document.getElementById('preview_media_img');
    const pVid      = document.getElementById('preview_media_vid');
    if (file.type.startsWith('image/')) {
        pImg.src = url; pImg.style.display = 'block';
        pVid.style.display = 'none';
    } else {
        pVid.src = url; pVid.style.display = 'block';
        pImg.style.display = 'none';
    }
    if (container) container.style.display = 'block';
}

function clearPostMedia() {
    const previewDiv = document.getElementById('postMediaPreview');
    const previewImg = document.getElementById('postMediaPreviewImg');
    const previewVid = document.getElementById('postMediaPreviewVid');
    const uploadArea = document.getElementById('postMediaUploadArea');
    const mediaInput = document.getElementById('media');
    if (previewImg) { previewImg.src = ''; previewImg.style.display = 'none'; }
    if (previewVid) { previewVid.src = ''; previewVid.style.display = 'none'; }
    if (previewDiv) previewDiv.style.display = 'none';
    if (uploadArea) uploadArea.style.display = 'flex';
    if (mediaInput) mediaInput.value = '';
    const container = document.getElementById('preview_media_container');
    const pImg      = document.getElementById('preview_media_img');
    const pVid      = document.getElementById('preview_media_vid');
    if (container) container.style.display = 'none';
    if (pImg) pImg.src = '';
    if (pVid) pVid.src = '';
}


document.addEventListener('DOMContentLoaded', function() {
 
    document.getElementById('post-content')?.addEventListener('input', updatePreview);
    document.querySelector('select[name="facebook_page_id"]')?.addEventListener('change', updatePreview);
    document.getElementById('scheduled_at')?.addEventListener('change', updatePreview);

 
    document.getElementById('openPageModalBtnQuick')?.addEventListener('click', () =>
        document.getElementById('pageModal').classList.replace('hidden','flex'));
    document.getElementById('closePageModalBtn')?.addEventListener('click', () =>
        document.getElementById('pageModal').classList.replace('flex','hidden'));
    document.getElementById('cancelPageModalBtn')?.addEventListener('click', () =>
        document.getElementById('pageModal').classList.replace('flex','hidden'));
});

function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
</script>