<div id="mediaLibraryModal" class="ml-modal-backdrop" dir="ltr">
    <div class="ml-modal-inner">


        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f3f4f6;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 style="font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:#0f1117;">Media Library</h3>
                    <p style="font-size:12px;color:#9ca3af;" id="mlTotalCount">Loading...</p>
                </div>
            </div>
            <button onclick="closeMediaLibrary()" style="color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Tabs & Search --}}
        <div style="padding:12px 24px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:10px;flex-shrink:0;flex-wrap:wrap;">
            <div style="display:flex;background:#f3f4f6;padding:4px;border-radius:10px;gap:2px;">
                <button class="post-type-tab active" data-tab="all" onclick="mlSetTab(this,'all')" style="font-size:12px;padding:5px 12px;">All</button>
                <button class="post-type-tab" data-tab="image" onclick="mlSetTab(this,'image')" style="font-size:12px;padding:5px 12px;">Images</button>
                <button class="post-type-tab" data-tab="video" onclick="mlSetTab(this,'video')" style="font-size:12px;padding:5px 12px;">Videos</button>
                <button class="post-type-tab" data-tab="upload" onclick="mlSetTab(this,'upload')" style="font-size:12px;padding:5px 12px;">+ Upload</button>
            </div>
            <div style="flex:1;min-width:140px;position:relative;">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="mlSearch" placeholder="Search files..." oninput="mlDebouncedSearch(this.value)" style="width:100%;padding:8px 12px 8px 32px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;" onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
        </div>

        {{-- Content --}}
        <div style="flex:1;overflow-y:auto;padding:20px 24px;" id="mlContent">
            <div id="mlGridView">
                <div id="mlGrid" class="ml-grid"></div>
                <div id="mlLoadMoreWrapper" style="text-align:center;margin-top:16px;display:none;">
                    <button onclick="mlLoadMore()" style="font-size:13px;color:#2563eb;font-weight:700;background:none;border:none;cursor:pointer;">Load more ↓</button>
                </div>
                <div id="mlEmpty" style="display:none;text-align:center;padding:48px 24px;color:#9ca3af;">
                    <p style="font-weight:600;font-size:14px;">No media found</p>
                </div>
            </div>
            <div id="mlUploadView" style="display:none;">
                <div id="mlDropZone" onclick="document.getElementById('mlFileInput').click()" ondragover="mlDragOver(event)" ondragleave="mlDragLeave(event)" ondrop="mlDrop(event)" style="border:2px dashed #d1d5db;border-radius:16px;padding:56px 24px;text-align:center;cursor:pointer;" onmouseover="this.style.borderColor='#2563eb';this.style.background='#eff6ff'" onmouseout="this.style.borderColor='#d1d5db';this.style.background='transparent'">
                    <svg width="48" height="48" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p style="font-size:14px;font-weight:700;color:#6b7280;">Drag & drop files here</p>
                    <p style="font-size:12px;color:#9ca3af;margin-top:4px;">or click to browse</p>
                    <input type="file" id="mlFileInput" class="hidden" multiple accept="image/*,video/*" onchange="mlUploadFiles(this.files)">
                </div>
                <div id="mlUploadProgress" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div id="mlFooter" style="padding:16px 24px;border-top:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <p id="mlSelectedInfo" style="font-size:12px;color:#9ca3af;font-weight:500;">No file selected</p>
            <div style="display:flex;gap:8px;">
                <button onclick="closeMediaLibrary()" style="padding:9px 18px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;font-weight:600;color:#6b7280;cursor:pointer;background:#fff;">Cancel</button>
                <button onclick="mlConfirmSelection()" id="mlConfirmBtn" disabled class="btn-primary" style="padding:9px 20px;opacity:.4;cursor:not-allowed;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Use this file
                </button>
            </div>
        </div>

    </div>
</div>