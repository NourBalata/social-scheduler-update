<script>
const ML = { page:1, type:'', search:'', selected:null, loading:false, timer:null };

async function mlFetch(url, options = {}) {
    return fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json', ...(options.headers || {}) },
    });
}

function openMediaLibrary() {
    document.getElementById('mediaLibraryModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    mlReset(); mlLoadMedia();
}
function closeMediaLibrary() {
    document.getElementById('mediaLibraryModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('mediaLibraryModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeMediaLibrary();
});

function mlSetTab(btn, tab) {
    document.querySelectorAll('#mediaLibraryModal .post-type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const isUpload = tab === 'upload';
    document.getElementById('mlGridView').style.display   = isUpload ? 'none'  : 'block';
    document.getElementById('mlUploadView').style.display = isUpload ? 'block' : 'none';
    document.getElementById('mlFooter').style.display     = isUpload ? 'none'  : 'flex';
    if (!isUpload) { ML.type = tab === 'all' ? '' : tab; mlReset(); mlLoadMedia(); }
}

function mlDebouncedSearch(v) {
    clearTimeout(ML.timer);
    ML.timer = setTimeout(() => { ML.search = v; mlReset(); mlLoadMedia(); }, 350);
}

function mlReset() {
    ML.page = 1; ML.selected = null;
    document.getElementById('mlGrid').innerHTML            = '';
    document.getElementById('mlEmpty').style.display      = 'none';
    document.getElementById('mlLoadMoreWrapper').style.display = 'none';
    mlUpdateFooter();
}

async function mlLoadMedia(append = false) {
    if (ML.loading) return;
    ML.loading = true;
    const grid = document.getElementById('mlGrid');
    if (!append) grid.innerHTML = Array(12).fill('<div class="ml-skeleton"></div>').join('');
    try {
        const params = new URLSearchParams({
            page: ML.page, per_page: 24,
            ...(ML.type   && { type:   ML.type }),
            ...(ML.search && { search: ML.search }),
        });
        const res = await mlFetch(`/media?${params}`);
        if (res.status === 401) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:32px;color:#ef4444;font-size:13px;">⚠️ Session expired.</div>';
            ML.loading = false; return;
        }
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data  = await res.json();
        const files = data.data || [];
        const meta  = data.meta || {};
        if (!append) grid.innerHTML = '';
        document.getElementById('mlTotalCount').textContent = `${meta.total ?? files.length} files`;
        if (!files.length && !append) {
            document.getElementById('mlEmpty').style.display = 'block';
        } else {
            files.forEach(f => grid.appendChild(mlCard(f)));
        }
        document.getElementById('mlLoadMoreWrapper').style.display =
            (meta.current_page ?? 1) < (meta.last_page ?? 1) ? 'block' : 'none';
    } catch(e) {
        if (!append) grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:32px;color:#9ca3af;font-size:13px;">Failed to load media.</div>';
        console.error(e);
    } finally { ML.loading = false; }
}

function mlCard(file) {
    const div     = document.createElement('div');
    div.className = 'ml-item';
    const isVideo = file.type === 'video';
    const thumb   = file.thumbnail_url || file.url;
    div.innerHTML = `
        <div class="ml-check"><svg width="10" height="10" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
        ${isVideo
            ? `<video src="${file.url}" poster="${thumb}" muted playsinline preload="none"></video>`
            : `<img src="${thumb}" alt="${file.name ?? ''}" loading="lazy">`
        }
        <div style="position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,.5);color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;text-transform:uppercase;">${file.type}</div>
    `;
    div.addEventListener('click', () => {
        document.querySelectorAll('.ml-item.selected').forEach(el => el.classList.remove('selected'));
        if (ML.selected?.id === file.id) {
            ML.selected = null;
        } else {
            div.classList.add('selected');
            ML.selected = { id:file.id, url:file.url, name:file.name ?? file.filename ?? 'media', type:file.type };
        }
        mlUpdateFooter();
    });
    return div;
}

function mlUpdateFooter() {
    const btn  = document.getElementById('mlConfirmBtn');
    const info = document.getElementById('mlSelectedInfo');
    if (ML.selected) {
        btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer';
        info.textContent = `✓ ${ML.selected.name}`; info.style.color = '#2563eb';
    } else {
        btn.disabled = true; btn.style.opacity = '.4'; btn.style.cursor = 'not-allowed';
        info.textContent = 'No file selected'; info.style.color = '#9ca3af';
    }
}

function mlConfirmSelection() {
    if (!ML.selected) return;
    document.getElementById('mediaLibraryId').value = ML.selected.id;
    document.getElementById('media').value          = '';
    showPostMediaPreview(ML.selected.url, ML.selected.name, ML.selected.type);
    closeMediaLibrary();
    showToast('Media selected from library');
}

function mlLoadMore() { ML.page++; mlLoadMedia(true); }

async function mlUploadFiles(files) {
    const prog = document.getElementById('mlUploadProgress');
    prog.innerHTML = ''; prog.style.display = 'flex';
    for (const file of Array.from(files)) {
        const key = 'f' + Math.random().toString(36).slice(2);
        const row = document.createElement('div');
        row.style.cssText = 'background:#f9fafb;border-radius:10px;padding:12px 14px;';
        row.innerHTML = `
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:12px;font-weight:600;color:#374151;">${file.name}</span>
                <span id="pct-${key}" style="font-size:12px;font-weight:700;color:#2563eb;">0%</span>
            </div>
            <div style="background:#e5e7eb;border-radius:99px;height:5px;overflow:hidden;">
                <div id="bar-${key}" style="height:100%;border-radius:99px;background:linear-gradient(90deg,#2563eb,#7c3aed);width:0%;transition:width .2s;"></div>
            </div>
        `;
        prog.appendChild(row);
        try {
            await new Promise((resolve, reject) => {
                const fd  = new FormData(); fd.append('file', file);
                const xhr = new XMLHttpRequest();
                xhr.upload.onprogress = e => {
                    if (e.lengthComputable) {
                        const p = Math.round(e.loaded / e.total * 100);
                        document.getElementById(`bar-${key}`).style.width = p + '%';
                        document.getElementById(`pct-${key}`).textContent = p + '%';
                    }
                };
                xhr.onload = () => {
                    if (xhr.status === 201 || xhr.status === 200) {
                        document.getElementById(`bar-${key}`).style.background = '#10b981';
                        document.getElementById(`pct-${key}`).textContent = '✓';
                        document.getElementById(`pct-${key}`).style.color = '#10b981';
                        resolve();
                    } else {
                        let errMsg = 'Upload failed';
                        try { errMsg = JSON.parse(xhr.responseText)?.message ?? errMsg; } catch {}
                        document.getElementById(`pct-${key}`).textContent = '✗ ' + errMsg;
                        document.getElementById(`pct-${key}`).style.color = '#ef4444';
                        reject(new Error(errMsg));
                    }
                };
                xhr.onerror = () => reject(new Error('Network error'));
                xhr.open('POST', '/media/upload');
                xhr.withCredentials = true;
                xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.send(fd);
            });
        } catch(e) { console.error('Upload error:', e); }
    }
    setTimeout(() => {
        const allTab = document.querySelector('[data-tab="all"]');
        if (allTab) allTab.click();
        mlReset(); mlLoadMedia();
    }, 1200);
}

function mlDragOver(e)  { e.preventDefault(); const z = document.getElementById('mlDropZone'); z.style.borderColor = '#2563eb'; z.style.background = '#eff6ff'; }
function mlDragLeave()  { const z = document.getElementById('mlDropZone'); z.style.borderColor = '#d1d5db'; z.style.background = 'transparent'; }
function mlDrop(e)      { e.preventDefault(); mlDragLeave(); mlUploadFiles(e.dataTransfer.files); }

function handleDirectUpload(input) {
    if (!input.files?.[0]) return;
    document.getElementById('mediaLibraryId').value = '';
    const file = input.files[0];
    showPostMediaPreview(URL.createObjectURL(file), file.name, file.type.startsWith('video') ? 'video' : 'image');
}

function showPostMediaPreview(url, name, type) {
    document.getElementById('postMediaUploadArea').style.display = 'none';
    document.getElementById('postMediaPreview').style.display    = 'block';
    const img = document.getElementById('postMediaPreviewImg');
    const vid = document.getElementById('postMediaPreviewVid');
    img.style.display = 'none'; vid.style.display = 'none';
    if (type === 'video') { vid.src = url; vid.style.display = 'block'; }
    else                  { img.src = url; img.style.display = 'block'; }
    document.getElementById('postMediaPreviewName').textContent = name;
}

function clearPostMedia() {
    document.getElementById('media').value                     = '';
    document.getElementById('mediaLibraryId').value           = '';
    document.getElementById('postMediaPreviewImg').src        = '';
    document.getElementById('postMediaPreviewVid').src        = '';
    document.getElementById('postMediaPreview').style.display = 'none';
    document.getElementById('postMediaUploadArea').style.display = 'flex';
}
</script>