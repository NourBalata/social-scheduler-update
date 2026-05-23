<div class="post-card">
    <div class="post-card-header">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </div>
        <h3>{{ __('Create New Post') }}</h3>
    </div>
    <div class="post-card-body">
        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
            @csrf

            {{-- Post Type Tabs --}}
            <div class="post-type-tabs">
                <button type="button" class="post-type-tab active" onclick="setPostType(this,'image')">🖼️ {{ __('Image')}}</button>
                <button type="button" class="post-type-tab" onclick="setPostType(this,'video')">🎥 {{ __('Video')}}</button>
                <button type="button" class="post-type-tab" onclick="setPostType(this,'text')">✍️ {{ __('Text Only')}}</button>
            </div>
            <input type="hidden" name="post_type" id="post_type_hidden" value="image">

            {{-- Page & Schedule --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label class="dash-label">{{ __('Select Page')}}</label>
                    <select name="facebook_page_id" class="dash-input">
                        <option value="">{{ __('— Choose a page —')}}</option>
                        @foreach($user->facebookPages as $page)
                            <option value="{{ $page->id }}" {{ old('facebook_page_id') == $page->id ? 'selected' : '' }}>
                                {{ $page->page_name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="openPageModalBtnQuick" style="margin-top:6px;font-size:12px;font-weight:700;color:#2563eb;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">+ {{ __('Add New Page')}}</button>
                </div>
                <div>
                    <label class="dash-label">{{ __('Schedule Time')}}</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" class="dash-input">
                    <button type="button" onclick="fillBestTime()" style="margin-top:6px;font-size:11px;font-weight:700;color:#7c3aed;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">⚡ {{ __('Use best time')}}</button>
                </div>
            </div>

            {{-- Media Section --}}
            <div id="mediaSection" style="margin-bottom:20px;">
                <label class="dash-label">{{ __('Post Media')}}</label>

                <div id="postMediaPreview" style="display:none;margin-bottom:10px;position:relative;">
                    <div style="border-radius:12px;overflow:hidden;border:1.5px solid #bfdbfe;background:#f0f7ff;max-height:200px;">
                        <img id="postMediaPreviewImg" src="" style="width:100%;max-height:200px;object-fit:cover;display:none;">
                        <video id="postMediaPreviewVid" src="" style="width:100%;max-height:200px;display:none;" muted playsinline></video>
                    </div>
                    <p id="postMediaPreviewName" style="font-size:11px;color:#6b7280;text-align:center;margin-top:6px;font-weight:500;"></p>
                    <button type="button" onclick="clearPostMedia()" style="position:absolute;top:8px;right:8px;width:28px;height:28px;background:#fff;border:1px solid #fca5a5;border-radius:50%;color:#ef4444;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div id="postMediaUploadArea" style="display:flex;gap:10px;">
                    <label style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #d1d5db;border-radius:14px;padding:20px 12px;cursor:pointer;background:#fafafa;transition:all .2s;text-align:center;" onmouseover="this.style.borderColor='#2563eb';this.style.background='#eff6ff'" onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fafafa'">
                        <svg width="26" height="26" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span style="font-size:12px;font-weight:700;color:#6b7280;">{{ __('Upload file')}}</span>
                        <span style="font-size:10px;color:#9ca3af;margin-top:2px;">JPG, PNG, MP4</span>
                        <input type="file" name="media" id="media" class="hidden" accept="image/*,video/*" onchange="handleDirectUpload(this)">
                    </label>
                    <button type="button" onclick="openMediaLibrary()" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px solid #bfdbfe;border-radius:14px;padding:20px 12px;cursor:pointer;background:#eff6ff;transition:all .2s;text-align:center;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                        <div style="width:36px;height:36px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:6px;">
                            <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span style="font-size:12px;font-weight:700;color:#2563eb;">{{ __('Media Library')}}</span>
                        <span style="font-size:10px;color:#60a5fa;margin-top:2px;">Choose from saved files</span>
                    </button>
                </div>
                <input type="hidden" name="media_library_id" id="mediaLibraryId">
            </div>

            {{-- Content --}}
            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <label class="dash-label" style="margin:0;">{{ __('Post Content')}}</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="char-counter" id="charCounter">0 / 2200</span>
                        <button type="button" id="ai-magic-btn" class="btn-magic">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            ✨ {{ __('Magic Write')}}
                        </button>
                    </div>
                </div>
                <div style="position:relative;">
                    <textarea name="content" id="post-content" required class="dash-textarea" placeholder="{{ __('Write your post here... or give a quick idea and click ✨ Magic Write')}}" oninput="updateCharCount(this)">{{ old('content') }}</textarea>
                    <div id="ai-loader" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.7);backdrop-filter:blur(2px);border-radius:12px;align-items:center;justify-content:center;z-index:10;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <svg style="animation:spin 1s linear infinite;width:32px;height:32px;color:#7c3aed;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg>
                            <span style="font-size:12px;font-weight:700;color:#7c3aed;">AI is thinking...</span>
                        </div>
                    </div>
                </div>
                <div style="margin-top:8px;" id="hashtagSection">
                    <p style="font-size:11px;color:#9ca3af;font-weight:600;margin-bottom:6px;">✨ {{ __('Suggested hashtags')}}:</p>
                    <div class="ai-suggestions" id="hashtagSuggestions"></div>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <button type="submit" class="btn-primary">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                   {{ __('Schedule Post')}} 
                </button>
              <div style="display:flex;gap:8px;">
    <button type="button" onclick="saveAsTemplate()" style="font-size:13px;font-weight:600;color:#7c3aed;background:#f5f3ff;border:1.5px solid #ddd6fe;padding:10px 18px;border-radius:10px;cursor:pointer;">💾 {{ __('Save as Template')}}</button>
    <button type="button" onclick="openTemplates()" style="font-size:13px;font-weight:600;color:#2563eb;background:#eff6ff;border:1.5px solid #bfdbfe;padding:10px 18px;border-radius:10px;cursor:pointer;">📋 {{ __('My Templates')}}</button>
    
</div>
            </div>
        </form>
        <div style="margin-top:24px;border-top:1.5px solid #e5e7eb;padding-top:20px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
        <div style="width:28px;height:28px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </div>
        <h4 style="font-size:14px;font-weight:700;color:#1e293b;margin:0;">{{ __('Live Preview')}}</h4>
        <span style="font-size:11px;color:#9ca3af;font-weight:500;">— {{ __('Facebook post appearance')}}</span>
    </div>

    <div style="background:#fff;border:1.5px solid #e4e6ea;border-radius:12px;overflow:hidden;max-width:500px;margin:0 auto;font-family:'Segoe UI',sans-serif;">

        {{-- Header --}}
        <div style="padding:12px 16px;display:flex;align-items:center;gap:10px;">
            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V10h2v6zm4 0h-2v-4c0-.55-.45-1-1-1s-1 .45-1 1v4h-2v-6h2v1.1c.4-.66 1.17-1.1 2-1.1 1.66 0 3 1.34 3 3v3z"/></svg>
            </div>
            <div style="flex:1;">
                <p id="preview_page_name" style="font-size:14px;font-weight:700;color:#1c1e21;margin:0;">{{ __('Your Page Name')}}</p>
                <div style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                    <span id="preview_time" style="font-size:12px;color:#65676b;">Just now</span>
                    <span style="color:#65676b;font-size:10px;">·</span>
                    <svg width="12" height="12" fill="#65676b" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div style="padding:0 16px 12px;">
            <p id="preview_content" style="font-size:14px;color:#1c1e21;line-height:1.6;margin:0;white-space:pre-wrap;word-break:break-word;">{{ __('Your post content will appear here')}}...</p>
        </div>

        {{-- Media --}}
        <div id="preview_media_container" style="display:none;">
            <img id="preview_media_img" src="" style="width:100%;max-height:300px;object-fit:cover;display:none;">
            <video id="preview_media_vid" src="" style="width:100%;max-height:300px;display:none;" muted playsinline controls></video>
        </div>

        {{-- Actions --}}
        <div style="padding:8px 16px;border-top:1px solid #e4e6ea;display:flex;justify-content:space-around;">
            <button style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:6px;border:none;background:none;cursor:pointer;color:#65676b;font-size:13px;font-weight:600;border-radius:4px;" onmouseover="this.style.background='#f2f2f2'" onmouseout="this.style.background='none'">
                👍 {{ __('Like')}}
            </button>
            <button style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:6px;border:none;background:none;cursor:pointer;color:#65676b;font-size:13px;font-weight:600;border-radius:4px;" onmouseover="this.style.background='#f2f2f2'" onmouseout="this.style.background='none'">
                💬 {{ __('Comment')}}
            </button>
            <button style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:6px;border:none;background:none;cursor:pointer;color:#65676b;font-size:13px;font-weight:600;border-radius:4px;" onmouseover="this.style.background='#f2f2f2'" onmouseout="this.style.background='none'">
                ↗️ {{ __('Share')}}
            </button>
        </div>
    </div>
</div>
    </div>
</div>