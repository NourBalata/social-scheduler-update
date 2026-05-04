<x-app-layout>

<x-slot name="header">
    <div style="display:flex;align-items:center;justify-content:space-between;" dir="ltr">
        <span class="brand" style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,#2563eb,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            ⚡ PostFlow
        </span>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="user-chip">
                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</x-slot>

<div style="position:relative;min-height:100vh;">
    <div class="dash-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="dash-wrap" dir="ltr">

        @if(session('success'))
            <div style="background:#d1fae5;color:#065f46;padding:14px 18px;border-radius:12px;margin-bottom:24px;border:1px solid #a7f3d0;font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff;">
                    <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <div class="stat-num">{{ auth()->user()->remainingPostsCount() }}</div>
                    <div class="stat-label">Remaining Posts</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4;">
                    <svg width="22" height="22" fill="#10b981" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </div>
                <div>
                    <div class="stat-num">{{ auth()->user()->facebookPages->count() }}</div>
                    <div class="stat-label">Linked Pages</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fffbeb;">
                    <svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="stat-num" id="stat-scheduled">—</div>
                    <div class="stat-label">Scheduled</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4;">
                    <svg width="22" height="22" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="stat-num" id="stat-published">—</div>
                    <div class="stat-label">Published</div>
                </div>
            </div>
        </div>

        {{-- Main Grid --}}
        <div class="main-grid">

            {{-- Sidebar --}}
            <div>
                <div class="sidebar-card">
                    <div class="sidebar-section-title">Active Pages</div>
                    @forelse(auth()->user()->facebookPages as $page)
                        <div class="page-item">
                            <span style="font-size:13px;font-weight:600;color:#111827;">{{ $page->page_name }}</span>
                            <div class="page-dot"></div>
                        </div>
                    @empty
                        <div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;font-style:italic;">No connected pages.</div>
                    @endforelse

                    <div class="best-times">
                        <div class="best-times-title">⏰ Best Times to Post</div>
                        <div class="time-slot"><span class="time-slot-label">9:00 AM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:88%"></div></div><span class="time-slot-pct">88%</span></div>
                        <div class="time-slot"><span class="time-slot-label">12:00 PM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:74%"></div></div><span class="time-slot-pct">74%</span></div>
                        <div class="time-slot"><span class="time-slot-label">6:00 PM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:95%"></div></div><span class="time-slot-pct">95%</span></div>
                        <div class="time-slot"><span class="time-slot-label">9:00 PM</span><div class="time-slot-bar-wrap"><div class="time-slot-bar" style="width:61%"></div></div><span class="time-slot-pct">61%</span></div>
                        <p style="font-size:10px;color:#9ca3af;margin-top:8px;">Based on global engagement patterns</p>
                    </div>

                    <div class="quick-actions">
                        <div class="sidebar-section-title" style="padding:0 0 8px;">Quick Actions</div>
                        <button class="quick-btn" onclick="document.getElementById('openPageModalBtnQuick').click()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add New Page
                        </button>
                        <button class="quick-btn" onclick="openMediaLibrary()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Media Library
                        </button>
                        <button class="quick-btn" onclick="fillBestTime()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Use Best Time
                        </button>
                    </div>
                </div>
            </div>

            {{-- Center Column --}}
            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- AI Autopilot Banner --}}
                <div style="background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:16px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <span style="font-size:20px;">🤖</span>
                            <h3 style="font-family:'Syne',sans-serif;font-size:16px;font-weight:800;color:#fff;margin:0;">AI Content Autopilot</h3>
                            <span style="background:#7c3aed;color:#e9d5ff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;">NEW</span>
                        </div>
                        <p style="font-size:12px;color:#a5b4fc;margin:0;">Schedule a full month of content in one click — educational, entertaining, and promotional.</p>
                    </div>
                    <button onclick="openAutopilotModal()" style="background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;font-weight:700;font-size:13px;padding:12px 22px;border-radius:12px;border:none;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Get Started
                    </button>
                </div>

                {{-- Create Post --}}
                <div class="post-card">
                    <div class="post-card-header">
                        <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <h3>Create New Post</h3>
                    </div>
                    <div class="post-card-body">
                        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
                            @csrf
                            <div class="post-type-tabs">
                                <button type="button" class="post-type-tab active" onclick="setPostType(this,'image')">🖼️ Image</button>
                                <button type="button" class="post-type-tab" onclick="setPostType(this,'video')">🎥 Video</button>
                                <button type="button" class="post-type-tab" onclick="setPostType(this,'text')">✍️ Text Only</button>
                            </div>
                            <input type="hidden" name="post_type" id="post_type_hidden" value="image">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                                <div>
                                    <label class="dash-label">Select Page</label>
                                    <input type="text" name="page_name" list="existing_pages" placeholder="Choose or type page name" value="{{ old('page_name') }}" class="dash-input">
                                    <datalist id="existing_pages">
                                        @foreach(auth()->user()->facebookPages as $page)
                                            <option value="{{ $page->page_name }}">
                                        @endforeach
                                    </datalist>
                                    <button type="button" id="openPageModalBtnQuick" style="margin-top:6px;font-size:12px;font-weight:700;color:#2563eb;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">+ Add new page</button>
                                </div>
                                <div>
                                    <label class="dash-label">Schedule Time</label>
                                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" class="dash-input">
                                    <button type="button" onclick="fillBestTime()" style="margin-top:6px;font-size:11px;font-weight:700;color:#7c3aed;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">⚡ Use best time (6:00 PM today)</button>
                                </div>
                            </div>
                            <div id="mediaSection" style="margin-bottom:20px;">
                                <label class="dash-label">Post Media</label>
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
                                        <span style="font-size:12px;font-weight:700;color:#6b7280;">Upload file</span>
                                        <span style="font-size:10px;color:#9ca3af;margin-top:2px;">JPG, PNG, MP4</span>
                                        <input type="file" name="media" id="media" class="hidden" accept="image/*,video/*" onchange="handleDirectUpload(this)">
                                    </label>
                                    <button type="button" onclick="openMediaLibrary()" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px solid #bfdbfe;border-radius:14px;padding:20px 12px;cursor:pointer;background:#eff6ff;transition:all .2s;text-align:center;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                        <div style="width:36px;height:36px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:6px;">
                                            <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <span style="font-size:12px;font-weight:700;color:#2563eb;">Media Library</span>
                                        <span style="font-size:10px;color:#60a5fa;margin-top:2px;">Choose from saved files</span>
                                    </button>
                                </div>
                                <input type="hidden" name="media_library_id" id="mediaLibraryId">
                            </div>
                            <div style="margin-bottom:20px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <label class="dash-label" style="margin:0;">Post Content</label>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="char-counter" id="charCounter">0 / 2200</span>
                                        <button type="button" id="ai-magic-btn" class="btn-magic">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            ✨ Magic Write
                                        </button>
                                    </div>
                                </div>
                                <div style="position:relative;">
                                    <textarea name="content" id="post-content" required class="dash-textarea" placeholder="Write your post here... or give a quick idea and click ✨ Magic Write" oninput="updateCharCount(this)">{{ old('content') }}</textarea>
                                    <div id="ai-loader" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.7);backdrop-filter:blur(2px);border-radius:12px;align-items:center;justify-content:center;z-index:10;">
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                            <svg style="animation:spin 1s linear infinite;width:32px;height:32px;color:#7c3aed;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg>
                                            <span style="font-size:12px;font-weight:700;color:#7c3aed;">AI is thinking...</span>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top:8px;" id="hashtagSection">
                                    <p style="font-size:11px;color:#9ca3af;font-weight:600;margin-bottom:6px;">✨ Suggested hashtags:</p>
                                    <div class="ai-suggestions" id="hashtagSuggestions"></div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                <button type="submit" class="btn-primary">
                                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    Schedule Post
                                </button>
                                <div style="display:flex;gap:8px;">
                                    <button type="button" onclick="saveAsTemplate()" style="font-size:13px;font-weight:600;color:#7c3aed;background:#f5f3ff;border:1.5px solid #e0d9ff;padding:10px 18px;border-radius:10px;cursor:pointer;">💾 Save as Template</button>
                                    <button type="button" onclick="saveDraft()" style="font-size:13px;font-weight:600;color:#6b7280;background:var(--mist);border:1.5px solid var(--steel);padding:10px 18px;border-radius:10px;cursor:pointer;">Save Draft</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Bulk CSV --}}
                <div class="bulk-card">
                    <h3 style="font-size:15px;font-weight:800;color:#065f46;margin-bottom:6px;display:flex;align-items:center;gap:8px;">
                        <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Bulk Schedule via CSV
                    </h3>
                    <p style="font-size:12px;color:#6b7280;margin-bottom:14px;">Columns: <code style="background:#dcfce7;padding:1px 6px;border-radius:4px;font-size:11px;">page_name, content, scheduled_at</code></p>
                    <form action="{{ route('posts.bulk') }}" method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;">
                        @csrf
                        <label style="flex:1;min-width:200px;display:flex;align-items:center;gap:10px;border:2px dashed #6ee7b7;border-radius:12px;padding:12px 16px;cursor:pointer;background:#f0fdf4;transition:all .2s;" onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='#6ee7b7'">
                            <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span style="font-size:13px;color:#047857;font-weight:600;" id="csv-label">Choose CSV file...</span>
                            <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="document.getElementById('csv-label').textContent=this.files[0].name">
                        </label>
                        <button type="submit" style="background:#10b981;color:#fff;font-weight:700;font-size:13px;padding:12px 20px;border-radius:12px;border:none;cursor:pointer;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload & Schedule
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="calendar-wrap">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                <h2 style="font-size:18px;font-weight:800;color:var(--ink);display:flex;align-items:center;gap:8px;">
                    <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Content Calendar
                </h2>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:12px;font-weight:700;color:#6b7280;">
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#3b82f6;display:inline-block;"></span>Scheduled</span>
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#10b981;display:inline-block;"></span>Published</span>
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;"></span>Failed</span>
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#8b5cf6;display:inline-block;"></span>Educational</span>
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>Promotional</span>
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#ec4899;display:inline-block;"></span>Entertainment</span>
                    <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#06b6d4;display:inline-block;"></span>Engagement</span>
                </div>
            </div>
            <div id="fc-calendar"></div>
        </div>

    </div>
</div>

{{-- MODALS --}}

{{-- Add Page Modal --}}
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

{{-- Post Details Modal --}}
<div id="calModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:460px;width:100%;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #f3f4f6;">
            <h3 style="font-family:'Syne',sans-serif;font-weight:800;color:#0f1117;">Post Details</h3>
            <button onclick="closeCalModal()" style="color:#9ca3af;border:none;background:none;cursor:pointer;"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span id="cal-badge" style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;"></span>
                <span id="cal-type-badge" style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;display:none;"></span>
                <span id="cal-time" style="font-size:13px;color:#9ca3af;"></span>
            </div>
            <div>
                <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Page</p>
                <p id="cal-page" style="font-size:14px;font-weight:600;color:#111827;"></p>
            </div>
            <div>
                <p style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Content</p>
                <p id="cal-content" style="font-size:13px;color:#374151;line-height:1.6;background:#f9fafb;border-radius:10px;padding:12px;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></p>
            </div>
        </div>
    </div>
</div>

{{-- Autopilot Modal --}}
<div id="autopilotModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:24px;box-shadow:0 32px 80px rgba(0,0,0,.25);max-width:560px;width:100%;max-height:90vh;display:flex;flex-direction:column;">

        <div style="padding:22px 28px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:22px;">🤖</span>
                <div>
                    <h3 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#0f1117;margin:0;">AI Content Autopilot</h3>
                    <p style="font-size:12px;color:#9ca3af;margin:0;">Full monthly content plan in one click</p>
                </div>
            </div>
            <button onclick="closeAutopilotModal()" style="color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div style="flex:1;overflow-y:auto;">

            {{-- Step 1: Form --}}
            <div id="apStep1" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="dash-label">Business Name *</label>
                        <input type="text" id="ap-business" class="dash-input" placeholder="e.g. Al-Asala Restaurant">
                    </div>
                    <div>
                        <label class="dash-label">Industry / Sector *</label>
                        <input type="text" id="ap-industry" class="dash-input" placeholder="e.g. Restaurants, Fashion, Real Estate">
                    </div>
                    <div>
                        <label class="dash-label">Target Audience *</label>
                        <input type="text" id="ap-audience" class="dash-input" placeholder="e.g. Young adults 18-35 in Saudi Arabia">
                    </div>
                    <div>
                        <label class="dash-label">Content Goal *</label>
                        <input type="text" id="ap-goal" class="dash-input" placeholder="e.g. Increase sales, grow followers">
                    </div>
                    <div>
                        <label class="dash-label">Page *</label>
                        <select id="ap-page" class="dash-input">
                            <option value="">Select a page</option>
                            @foreach(auth()->user()->facebookPages as $page)
                                <option value="{{ $page->page_name }}">{{ $page->page_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="dash-label">Posts per Week *</label>
                        <select id="ap-ppw" class="dash-input">
                            <option value="3">3 posts / week (12/month)</option>
                            <option value="4">4 posts / week (16/month)</option>
                            <option value="5" selected>5 posts / week (20/month)</option>
                            <option value="7">7 posts / week (28/month)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="dash-label">Content Style *</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;" id="ap-tone-grid">
                        <button type="button" class="ap-tone-btn active" data-tone="friendly" onclick="selectTone(this)">😊 Friendly & Casual</button>
                        <button type="button" class="ap-tone-btn" data-tone="formal" onclick="selectTone(this)">💼 Formal & Professional</button>
                        <button type="button" class="ap-tone-btn" data-tone="humorous" onclick="selectTone(this)">😄 Humorous & Light</button>
                        <button type="button" class="ap-tone-btn" data-tone="inspiring" onclick="selectTone(this)">🔥 Inspiring & Motivational</button>
                    </div>
                </div>
                <div id="ap-error" style="display:none;background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:500;"></div>
                <button onclick="startAutopilot()" id="ap-generate-btn" style="background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;font-weight:700;font-size:14px;padding:14px;border-radius:12px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Generate Full Month Plan
                </button>
            </div>

            {{-- Step 2: Loading --}}
            <div id="apStep2" style="display:none;padding:48px 28px;text-align:center;">
                <svg style="animation:spin 1s linear infinite;width:48px;height:48px;color:#7c3aed;margin:0 auto 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg>
                <p style="font-size:16px;font-weight:700;color:#111827;margin-bottom:8px;">AI is generating your plan...</p>
                <p style="font-size:13px;color:#9ca3af;" id="ap-loading-msg">Analyzing your business and crafting the right content</p>
                <div style="margin-top:20px;background:#f3f4f6;border-radius:99px;height:6px;overflow:hidden;">
                    <div id="ap-progress-bar" style="height:100%;background:linear-gradient(90deg,#7c3aed,#2563eb);border-radius:99px;width:0%;transition:width .3s;"></div>
                </div>
            </div>

            {{-- Step 3: Preview --}}
            <div id="apStep3" style="display:none;padding:24px 28px;">
                <div id="ap-summary" style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:12px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#065f46;font-weight:600;"></div>
                <div style="max-height:320px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;margin-bottom:16px;" id="ap-posts-preview"></div>
                <div style="display:flex;gap:10px;">
                    <button onclick="showApStep(1)" style="flex:1;padding:12px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:13px;font-weight:600;color:#6b7280;cursor:pointer;background:#fff;">← Regenerate</button>
                    <button onclick="confirmAutopilot()" id="ap-confirm-btn" style="flex:2;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-weight:700;font-size:14px;padding:12px;border-radius:12px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Schedule All Now
                    </button>
                </div>
            </div>

            {{-- Step 4: Success --}}
            <div id="apStep4" style="display:none;padding:48px 28px;text-align:center;">
                <div style="width:64px;height:64px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg width="32" height="32" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;" id="ap-success-msg">Your plan has been scheduled! 🎉</p>
                <p style="font-size:13px;color:#9ca3af;margin-bottom:24px;">All posts are now visible in the calendar below.</p>
                <button onclick="closeAutopilotModal()" style="background:#f3f4f6;color:#374151;font-weight:700;font-size:14px;padding:12px 28px;border-radius:12px;border:none;cursor:pointer;">Got it, view calendar</button>
            </div>

        </div>
    </div>
</div>

{{-- Date Click Modal --}}
<div id="dateClickModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:24px;box-shadow:0 32px 80px rgba(0,0,0,.25);max-width:500px;width:100%;max-height:90vh;display:flex;flex-direction:column;">

        <div style="padding:20px 24px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div>
                <h3 style="font-family:'Syne',sans-serif;font-size:17px;font-weight:800;color:#0f1117;margin:0;">✨ Create Post with AI</h3>
                <p style="font-size:12px;color:#9ca3af;margin:0;" id="dc-date-label">Select a day from the calendar</p>
            </div>
            <button onclick="closeDateClickModal()" style="color:#9ca3af;border:none;background:#f3f4f6;border-radius:8px;width:32px;height:32px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div style="flex:1;overflow-y:auto;padding:20px 24px;display:flex;flex-direction:column;gap:14px;">

            <div id="dcFormArea">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="dash-label">Business Name *</label>
                        <input type="text" id="dc-business" class="dash-input" placeholder="Al-Asala Restaurant">
                    </div>
                    <div>
                        <label class="dash-label">Industry *</label>
                        <input type="text" id="dc-industry" class="dash-input" placeholder="Restaurants, Fashion...">
                    </div>
                    <div>
                        <label class="dash-label">Audience *</label>
                        <input type="text" id="dc-audience" class="dash-input" placeholder="Young adults 18-35">
                    </div>
                    <div>
                        <label class="dash-label">Tone *</label>
                        <select id="dc-tone" class="dash-input">
                            <option value="friendly">😊 Friendly & Casual</option>
                            <option value="formal">💼 Formal & Professional</option>
                            <option value="humorous">😄 Humorous & Light</option>
                            <option value="inspiring">🔥 Inspiring & Motivational</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="dash-label">Post Type *</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;" id="dc-type-grid">
                        <button type="button" class="ap-tone-btn active" data-type="educational" onclick="selectDcType(this)">📚 Educational</button>
                        <button type="button" class="ap-tone-btn" data-type="promotional" onclick="selectDcType(this)">🛍️ Promotional</button>
                        <button type="button" class="ap-tone-btn" data-type="entertainment" onclick="selectDcType(this)">🎉 Entertainment</button>
                        <button type="button" class="ap-tone-btn" data-type="engagement" onclick="selectDcType(this)">💬 Engagement</button>
                    </div>
                </div>
                <div id="dc-error" style="display:none;background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:8px;"></div>
                <button onclick="generateDcPost()" id="dc-gen-btn" style="width:100%;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;font-weight:700;font-size:14px;padding:13px;border-radius:12px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <svg style="animation:spin 1s linear infinite;display:none;" id="dc-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg>
                    ✨ Generate Post
                </button>
            </div>

            <div id="dcResultArea" style="display:none;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <label class="dash-label" style="margin:0;">Generated Post</label>
                    <button onclick="regenerateDcPost()" style="font-size:12px;color:#7c3aed;background:none;border:none;cursor:pointer;font-weight:600;">↻ Regenerate</button>
                </div>
                <textarea id="dc-content" class="dash-textarea" style="min-height:140px;margin-bottom:12px;" oninput="updateDcCharCount(this)"></textarea>
                <p id="dc-char-count" style="font-size:11px;color:#9ca3af;margin-bottom:12px;text-align:right;">0 / 2200</p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="dash-label">Page *</label>
                        <select id="dc-page" class="dash-input">
                            @foreach(auth()->user()->facebookPages as $page)
                                <option value="{{ $page->page_name }}">{{ $page->page_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="dash-label">Publish Time *</label>
                        <input type="datetime-local" id="dc-scheduled-at" class="dash-input">
                    </div>
                </div>

                <div id="dc-save-error" style="display:none;background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:8px;"></div>
                <button onclick="saveDcPost()" id="dc-save-btn" style="width:100%;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-weight:700;font-size:14px;padding:13px;border-radius:12px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <svg style="animation:spin 1s linear infinite;display:none;" id="dc-save-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg>
                    Schedule This Post
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Media Library Modal --}}
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
        <div style="flex:1;overflow-y:auto;padding:20px 24px;" id="mlContent">
            <div id="mlGridView">
                <div id="mlGrid" class="ml-grid"></div>
                <div id="mlLoadMoreWrapper" style="text-align:center;margin-top:16px;display:none;"><button onclick="mlLoadMore()" style="font-size:13px;color:#2563eb;font-weight:700;background:none;border:none;cursor:pointer;">Load more ↓</button></div>
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

<div id="templatesModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" dir="ltr">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:560px;width:100%;max-height:80vh;display:flex;flex-direction:column;">
        <div style="padding:20px 24px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <h3 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#0f1117;">💾 My Templates</h3>
            <button onclick="closeTemplates()" style="color:#9ca3af;border:none;background:none;cursor:pointer;"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div style="flex:1;overflow-y:auto;padding:20px 24px;" id="templatesList">
            <div style="text-align:center;color:#9ca3af;font-size:13px;padding:32px;">Loading...</div>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<style>
.ap-tone-btn {
    padding: 10px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    background: #fff;
    transition: all .15s;
    text-align: center;
}
.ap-tone-btn.active {
    border-color: #7c3aed;
    background: #f5f3ff;
    color: #7c3aed;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"></script>

<script>
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
</script>

</x-app-layout>