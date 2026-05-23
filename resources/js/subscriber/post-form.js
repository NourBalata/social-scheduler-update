/**
 * post-form.js
 * Handles: post type tabs, char counter, draft saving,
 *          Magic Write AI, caption picker, hashtag chips,
 *          media upload/preview, page modal wiring.
 *
 * Depends on: window.AppConfig.routes (set in dashboard.blade.php)
 */

const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ─── Toast ────────────────────────────────────────────────────────────────────
window.showToast = function (msg, duration = 2800) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), duration);
};

// ─── Page Modal ───────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const pageModal   = document.getElementById('pageModal');
    const openBtn     = document.getElementById('openPageModalBtnQuick');
    const closeBtn    = document.getElementById('closePageModalBtn');
    const cancelBtn   = document.getElementById('cancelPageModalBtn');

    openBtn?.addEventListener('click',  () => pageModal?.classList.replace('hidden', 'flex'));
    closeBtn?.addEventListener('click', () => pageModal?.classList.replace('flex', 'hidden'));
    cancelBtn?.addEventListener('click',() => pageModal?.classList.replace('flex', 'hidden'));
});

// ─── Post Type Tabs ───────────────────────────────────────────────────────────
window.setPostType = function (btn, type) {
    document.querySelectorAll('.post-type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('post_type_hidden').value = type;
    document.getElementById('mediaSection').style.display = type === 'text' ? 'none' : 'block';
};

// ─── Char Counter ─────────────────────────────────────────────────────────────
window.updateCharCount = function (el) {
    const len     = el.value.length;
    const counter = document.getElementById('charCounter');
    if (!counter) return;
    counter.textContent = `${len} / 2200`;
    counter.className   = 'char-counter'
        + (len > 2000 ? ' warn' : '')
        + (len > 2200 ? ' over' : '');
};

// ─── Best Time ────────────────────────────────────────────────────────────────
window.fillBestTime = function () {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const input = document.getElementById('scheduled_at');
    if (input) {
        input.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T18:00`;
    }
    showToast('⚡ Best time set: Today at 6:00 PM');
};

// ─── Draft ────────────────────────────────────────────────────────────────────
window.saveDraft = function () {
    const content = document.getElementById('post-content')?.value;
    if (!content?.trim()) return showToast('⚠️ Write something first!');
    localStorage.setItem('postflow_draft', content);
    showToast('✅ Draft saved locally');
};

// ─── Template (stub) ─────────────────────────────────────────────────────────
window.saveAsTemplate = function () {
    showToast('💾 Template saving coming soon');
};

// ─── Hashtag helpers ──────────────────────────────────────────────────────────
window.appendHashtag = function (tag) {
    const ta = document.getElementById('post-content');
    if (!ta) return;
    ta.value += (ta.value && !ta.value.endsWith(' ') ? ' ' : '') + tag + ' ';
    updateCharCount(ta);
    ta.focus();
};

window.removeHashtagFromContent = function (tag) {
    const ta = document.getElementById('post-content');
    if (!ta) return;
    ta.value = ta.value
        .replace(new RegExp('\\s?' + tag.replace('#', '\\#') + '\\s?', 'g'), ' ')
        .trim();
    updateCharCount(ta);
};

// ─── Magic Write ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const aiBtn          = document.getElementById('ai-magic-btn');
    const contentTextarea = document.getElementById('post-content');
    const aiLoader       = document.getElementById('ai-loader');

    aiBtn?.addEventListener('click', async () => {
        const text = contentTextarea.value.trim();
        if (text.length < 5) { showToast('💡 Type a quick idea first!'); return; }

        aiBtn.disabled           = true;
        aiLoader.style.display   = 'flex';

        try {
            const res  = await fetch(window.AppConfig.routes.aiCaption, {
                method:      'POST',
                headers:     { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin',
                body:        JSON.stringify({ idea: text }),
            });
            const data = await res.json();

            if (res.status === 429) { showToast('⏳ ' + (data.error ?? 'AI busy'), 4000); return; }
            if (data.captions?.length) showCaptionPicker(data.captions);
            if (data.hashtags?.length) renderHashtagChips(data.hashtags);

        } catch (e) {
            showToast('❌ AI connection failed');
        } finally {
            aiBtn.disabled         = false;
            aiLoader.style.display = 'none';
        }
    });
});

function renderHashtagChips(hashtags) {
    const container = document.getElementById('hashtagSuggestions');
    if (!container) return;
    container.innerHTML = '';
    hashtags.forEach(tag => {
        const chip      = document.createElement('span');
        chip.className  = 'ai-suggestion-chip';
        chip.textContent = tag;
        chip.onclick    = () => {
            if (chip.classList.contains('selected-hashtag')) {
                chip.classList.remove('selected-hashtag');
                chip.style.background = '';
                chip.style.color      = '';
                removeHashtagFromContent(tag);
            } else {
                chip.classList.add('selected-hashtag');
                chip.style.background = '#4f46e5';
                chip.style.color      = '#fff';
                appendHashtag(tag);
            }
        };
        container.appendChild(chip);
    });
}

function showCaptionPicker(captions) {
    document.getElementById('captionPicker')?.remove();

    const contentTextarea = document.getElementById('post-content');
    const picker          = document.createElement('div');
    picker.id             = 'captionPicker';
    picker.style.cssText  = 'background:#f8faff;border:1.5px solid #bfdbfe;border-radius:14px;padding:14px;margin-bottom:12px;display:flex;flex-direction:column;gap:8px;';

    const title           = document.createElement('p');
    title.style.cssText   = 'font-size:11px;font-weight:700;color:#4f46e5;margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;';
    title.textContent     = '✨ Choose a caption:';
    picker.appendChild(title);

    captions.forEach((caption, i) => {
        const btn          = document.createElement('button');
        btn.type           = 'button';
        btn.style.cssText  = 'text-align:left;padding:10px 14px;border-radius:10px;border:1.5px solid #e0e7ff;background:#fff;font-size:13px;color:#374151;cursor:pointer;line-height:1.5;';
        btn.innerHTML      = `<span style="font-size:10px;font-weight:800;color:#7c3aed;margin-right:6px;">Option ${i + 1}</span>${caption}`;
        btn.onclick        = () => {
            contentTextarea.value = '';
            let j = 0;
            const timer = setInterval(() => {
                if (j < caption.length) {
                    contentTextarea.value += caption[j++];
                    updateCharCount(contentTextarea);
                } else {
                    clearInterval(timer);
                }
            }, 14);
            picker.remove();
        };
        picker.appendChild(btn);
    });

    contentTextarea?.parentElement?.parentElement?.insertBefore(picker, contentTextarea.parentElement);
}

// ─── Media Preview ────────────────────────────────────────────────────────────
window.handleDirectUpload = function (input) {
    if (!input.files?.[0]) return;
    document.getElementById('mediaLibraryId').value = '';
    const file = input.files[0];
    showPostMediaPreview(
        URL.createObjectURL(file),
        file.name,
        file.type.startsWith('video') ? 'video' : 'image'
    );
};

window.showPostMediaPreview = function (url, name, type) {
    document.getElementById('postMediaUploadArea').style.display = 'none';
    document.getElementById('postMediaPreview').style.display    = 'block';

    const img = document.getElementById('postMediaPreviewImg');
    const vid = document.getElementById('postMediaPreviewVid');
    img.style.display = 'none';
    vid.style.display = 'none';

    if (type === 'video') {
        vid.src           = url;
        vid.style.display = 'block';
    } else {
        img.src           = url;
        img.style.display = 'block';
    }
    document.getElementById('postMediaPreviewName').textContent = name;
};

window.clearPostMedia = function () {
    document.getElementById('media').value                    = '';
    document.getElementById('mediaLibraryId').value           = '';
    document.getElementById('postMediaPreviewImg').src        = '';
    document.getElementById('postMediaPreviewVid').src        = '';
    document.getElementById('postMediaPreview').style.display = 'none';
    document.getElementById('postMediaUploadArea').style.display = 'flex';
};