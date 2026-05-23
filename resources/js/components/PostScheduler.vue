<template>
    <div class="post-card">
        <div class="post-card-header">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h3>Create New Post</h3>
        </div>

        <div class="post-card-body">
            <form :action="storeRoute" method="POST" enctype="multipart/form-data" @submit="handleSubmit">
                <input type="hidden" name="_token" :value="csrf">
                <input type="hidden" name="post_type" :value="postType">

                <!-- Post Type Tabs -->
                <div class="post-type-tabs">
                    <button type="button"
                        v-for="tab in tabs"
                        :key="tab.value"
                        class="post-type-tab"
                        :class="{ active: postType === tab.value }"
                        @click="postType = tab.value">
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Page & Schedule -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label class="dash-label">Select Page</label>
                        <input
                            type="text"
                            name="page_name"
                            list="existing_pages"
                            v-model="pageName"
                            placeholder="Choose or type page name"
                            class="dash-input">
                        <datalist id="existing_pages">
                            <option v-for="page in facebookPages" :key="page.id" :value="page.page_name"/>
                        </datalist>
                        <button type="button"
                            @click="$dispatch('open-page-modal')"
                            style="margin-top:6px;font-size:12px;font-weight:700;color:#2563eb;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                            + Add new page
                        </button>
                    </div>
                    <div>
                        <label class="dash-label">Schedule Time</label>
                        <input
                            type="datetime-local"
                            name="scheduled_at"
                            v-model="scheduledAt"
                            class="dash-input">
                        <button type="button"
                            @click="fillBestTime"
                            style="margin-top:6px;font-size:11px;font-weight:700;color:#7c3aed;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                            ⚡ Use best time (6:00 PM today)
                        </button>
                    </div>
                </div>

                <!-- Media Section -->
                <div v-if="postType !== 'text'" style="margin-bottom:20px;">
                    <label class="dash-label">Post Media</label>

                    <!-- Media Preview -->
                    <div v-if="mediaPreview" style="margin-bottom:10px;position:relative;">
                        <div style="border-radius:12px;overflow:hidden;border:1.5px solid #bfdbfe;background:#f0f7ff;max-height:200px;">
                            <img v-if="mediaPreviewType === 'image'" :src="mediaPreview" style="width:100%;max-height:200px;object-fit:cover;">
                            <video v-else :src="mediaPreview" style="width:100%;max-height:200px;" muted playsinline></video>
                        </div>
                        <p style="font-size:11px;color:#6b7280;text-align:center;margin-top:6px;font-weight:500;">{{ mediaFileName }}</p>
                        <button type="button" @click="clearMedia"
                            style="position:absolute;top:8px;right:8px;width:28px;height:28px;background:#fff;border:1px solid #fca5a5;border-radius:50%;color:#ef4444;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Upload Area -->
                    <div v-else style="display:flex;gap:10px;">
                        <label style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #d1d5db;border-radius:14px;padding:20px 12px;cursor:pointer;background:#fafafa;transition:all .2s;text-align:center;"
                            @mouseover="e => e.currentTarget.style.cssText += 'border-color:#2563eb;background:#eff6ff;'"
                            @mouseleave="e => e.currentTarget.style.cssText += 'border-color:#d1d5db;background:#fafafa;'">
                            <svg width="26" height="26" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:6px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span style="font-size:12px;font-weight:700;color:#6b7280;">Upload file</span>
                            <span style="font-size:10px;color:#9ca3af;margin-top:2px;">JPG, PNG, MP4</span>
                            <input type="file" name="media" class="hidden" accept="image/*,video/*" @change="handleFileUpload">
                        </label>
                        <button type="button" @click="$dispatch('open-media-library')"
                            style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px solid #bfdbfe;border-radius:14px;padding:20px 12px;cursor:pointer;background:#eff6ff;transition:all .2s;text-align:center;">
                            <div style="width:36px;height:36px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:6px;">
                                <svg width="18" height="18" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:#2563eb;">Media Library</span>
                            <span style="font-size:10px;color:#60a5fa;margin-top:2px;">Choose from saved files</span>
                        </button>
                    </div>
                    <input type="hidden" name="media_library_id" :value="mediaLibraryId">
                </div>

                <!-- Content -->
                <div style="margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <label class="dash-label" style="margin:0;">Post Content</label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="char-counter" :class="{ warn: content.length > 2000, over: content.length > 2200 }">
                                {{ content.length }} / 2200
                            </span>
                            <button type="button" class="btn-magic" :disabled="aiLoading" @click="magicWrite">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                ✨ Magic Write
                            </button>
                        </div>
                    </div>

                    <div style="position:relative;">
                        <textarea
                            name="content"
                            v-model="content"
                            required
                            class="dash-textarea"
                            placeholder="Write your post here... or give a quick idea and click ✨ Magic Write">
                        </textarea>

                        <!-- AI Loader -->
                        <div v-if="aiLoading"
                            style="position:absolute;inset:0;background:rgba(255,255,255,.7);backdrop-filter:blur(2px);border-radius:12px;display:flex;align-items:center;justify-content:center;z-index:10;">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                <svg style="animation:spin 1s linear infinite;width:32px;height:32px;color:#7c3aed;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
                                </svg>
                                <span style="font-size:12px;font-weight:700;color:#7c3aed;">AI is thinking...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hashtag Suggestions -->
                    <div v-if="hashtags.length" style="margin-top:8px;">
                        <p style="font-size:11px;color:#9ca3af;font-weight:600;margin-bottom:6px;">✨ Suggested hashtags:</p>
                        <div class="ai-suggestions">
                            <span
                                v-for="tag in hashtags"
                                :key="tag"
                                class="hashtag-chip"
                                :class="{ 'selected-hashtag': selectedHashtags.includes(tag) }"
                                @click="toggleHashtag(tag)">
                                {{ tag }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <button type="submit" class="btn-primary" :disabled="submitting || content.length === 0 || content.length > 2200">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        {{ submitting ? 'Scheduling...' : 'Schedule Post' }}
                    </button>
                    <div style="display:flex;gap:8px;">
                        <button type="button" @click="saveDraft"
                            style="font-size:13px;font-weight:600;color:#6b7280;background:var(--mist);border:1.5px solid var(--steel);padding:10px 18px;border-radius:10px;cursor:pointer;">
                            Save Draft
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Toast -->
        <div class="toast" :class="{ show: toast.show }">{{ toast.message }}</div>
    </div>
</template>

<script>
export default {
    name: 'PostScheduler',

    props: {
        csrf:         { type: String, required: true },
        storeRoute:   { type: String, required: true },
        aiRoute:      { type: String, required: true },
        facebookPages:{ type: Array,  default: () => [] },
    },

    emits: ['open-page-modal', 'open-media-library'],

    data() {
        const pad = n => String(n).padStart(2, '0');
        const now = new Date();
        const defaultTime = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours()+1)}:00`;

        return {
            postType:        'image',
            pageName:        '',
            scheduledAt:     defaultTime,
            content:         '',
            mediaPreview:    null,
            mediaPreviewType:'image',
            mediaFileName:   '',
            mediaLibraryId:  '',
            aiLoading:       false,
            submitting:      false,
            hashtags:        [],
            selectedHashtags:[],
            toast:           { show: false, message: '' },

            tabs: [
                { label: '🖼️ Image', value: 'image' },
                { label: '🎥 Video', value: 'video' },
                { label: '✍️ Text Only', value: 'text' },
            ],
        };
    },

    methods: {
        fillBestTime() {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            this.scheduledAt = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T18:00`;
            this.showToast('⚡ Best time set: Today at 6:00 PM');
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.mediaFileName   = file.name;
            this.mediaPreviewType = file.type.startsWith('video') ? 'video' : 'image';
            this.mediaPreview    = URL.createObjectURL(file);
        },

        clearMedia() {
            this.mediaPreview    = null;
            this.mediaFileName   = '';
            this.mediaLibraryId  = '';
        },

        toggleHashtag(tag) {
            if (this.selectedHashtags.includes(tag)) {
                this.selectedHashtags = this.selectedHashtags.filter(t => t !== tag);
                this.content = this.content.replace(' ' + tag, '').replace(tag, '').trim();
            } else {
                this.selectedHashtags.push(tag);
                this.content += (this.content && !this.content.endsWith(' ') ? ' ' : '') + tag + ' ';
            }
        },

        async magicWrite() {
            if (this.content.trim().length < 5) {
                this.showToast('💡 Type a quick idea first!');
                return;
            }
            this.aiLoading = true;
            try {
                const res = await fetch(this.aiRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    credentials: 'same-origin',
                    body: JSON.stringify({ idea: this.content.trim() }),
                });
                const data = await res.json();
                if (data.captions) {
                    this.content  = '';
                    const full    = data.captions[0];
                    let i         = 0;
                    const timer   = setInterval(() => {
                        if (i < full.length) this.content += full[i++];
                        else clearInterval(timer);
                    }, 18);
                }
                if (data.hashtags?.length) {
                    this.hashtags        = data.hashtags;
                    this.selectedHashtags = [];
                }
            } catch {
                this.showToast('AI connection failed');
            } finally {
                this.aiLoading = false;
            }
        },

        saveDraft() {
            if (!this.content.trim()) { this.showToast('⚠️ Write something first!'); return; }
            localStorage.setItem('postflow_draft', this.content);
            this.showToast('✅ Draft saved locally');
        },

        handleSubmit() {
            this.submitting = true;
        },

        showToast(msg, duration = 2800) {
            this.toast = { show: true, message: msg };
            setTimeout(() => { this.toast.show = false; }, duration);
        },
    },
};
</script>