<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="dc-overlay"
      @click.self="close"
      dir="ltr"
    >
      <div class="dc-modal">

        <!-- Header -->
        <div class="dc-header">
          <div>
            <h3 class="dc-title">✨ Create Post with AI</h3>
            <p class="dc-subtitle">{{ dateLabel }}</p>
          </div>
          <button class="dc-close-btn" @click="close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="dc-body">

          <!-- Form Area -->
          <div v-if="!showResult">
            <div class="dc-grid-2" style="margin-bottom:14px;">
              <div>
                <label class="dash-label">Business Name *</label>
                <input v-model="form.business" type="text" class="dash-input" placeholder="Al-Asala Restaurant">
              </div>
              <div>
                <label class="dash-label">Industry *</label>
                <input v-model="form.industry" type="text" class="dash-input" placeholder="Restaurants, Fashion...">
              </div>
              <div>
                <label class="dash-label">Audience *</label>
                <input v-model="form.audience" type="text" class="dash-input" placeholder="Young adults 18-35">
              </div>
              <div>
                <label class="dash-label">Tone *</label>
                <select v-model="form.tone" class="dash-input">
                  <option value="friendly">😊 Friendly & Casual</option>
                  <option value="formal">💼 Formal & Professional</option>
                  <option value="humorous">😄 Humorous & Light</option>
                  <option value="inspiring">🔥 Inspiring & Motivational</option>
                </select>
              </div>
            </div>

            <div style="margin-bottom:14px;">
              <label class="dash-label">Post Type *</label>
              <div class="dc-type-grid">
                <button
                  v-for="t in postTypes"
                  :key="t.value"
                  type="button"
                  class="ap-tone-btn"
                  :class="{ active: form.postType === t.value }"
                  @click="form.postType = t.value"
                >
                  {{ t.label }}
                </button>
              </div>
            </div>

            <div v-if="error" class="dc-error">{{ error }}</div>

            <button class="dc-btn-primary" :disabled="generating" @click="generatePost">
              <svg v-if="generating" class="dc-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
              </svg>
              {{ generating ? 'Generating...' : '✨ Generate Post' }}
            </button>
          </div>

          <!-- Result Area -->
          <div v-else>
            <div class="dc-result-header">
              <label class="dash-label" style="margin:0;">Generated Post</label>
              <button class="dc-regen-btn" @click="regenerate">↻ Regenerate</button>
            </div>

            <textarea
              v-model="generatedContent"
              class="dash-textarea"
              style="min-height:140px;margin-bottom:12px;"
            ></textarea>
            <p class="dc-char-count">{{ generatedContent.length }} / 2200</p>

            <div class="dc-grid-2" style="margin-bottom:14px;">
              <div>
                <label class="dash-label">Page *</label>
                <select v-model="form.page" class="dash-input">
                  <option v-for="p in pages" :key="p.page_name" :value="p.page_name">
                    {{ p.page_name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="dash-label">Publish Time *</label>
                <input v-model="scheduledAt" type="datetime-local" class="dash-input">
              </div>
            </div>

            <div v-if="saveError" class="dc-error">{{ saveError }}</div>

            <button class="dc-btn-success" :disabled="saving" @click="savePost">
              <svg v-if="!saving" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
              </svg>
              <svg v-else class="dc-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
              </svg>
              {{ saving ? 'Scheduling...' : 'Schedule This Post' }}
            </button>
          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

// ─── Props ────────────────────────────────────────────────────────
const props = defineProps({
  pages:               { type: Array,  default: () => [] },
  csrf:                { type: String, required: true },
  generateSingleRoute: { type: String, required: true },
  confirmSingleRoute:  { type: String, required: true },
})

// ─── Emits ────────────────────────────────────────────────────────
const emit = defineEmits(['post-saved'])

// ─── State ───────────────────────────────────────────────────────
const isOpen           = ref(false)
const selectedDate     = ref('')
const showResult       = ref(false)
const generating       = ref(false)
const saving           = ref(false)
const error            = ref('')
const saveError        = ref('')
const generatedContent = ref('')
const scheduledAt      = ref('')

const form = reactive({
  business: '',
  industry: '',
  audience: '',
  tone:     'friendly',
  postType: 'educational',
  page:     '',
})

// ─── Constants ───────────────────────────────────────────────────
const postTypes = [
  { value: 'educational',   label: '📚 Educational' },
  { value: 'promotional',   label: '🛍️ Promotional' },
  { value: 'entertainment', label: '🎉 Entertainment' },
  { value: 'engagement',    label: '💬 Engagement' },
]

// ─── Computed ────────────────────────────────────────────────────
const dateLabel = computed(() => {
  if (!selectedDate.value) return 'Select a day from the calendar'
  return new Date(selectedDate.value).toLocaleDateString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
  })
})

// ─── Methods ─────────────────────────────────────────────────────
function open(dateStr) {
  selectedDate.value  = dateStr
  scheduledAt.value   = dateStr + 'T18:00'
  showResult.value    = false
  error.value         = ''
  saveError.value     = ''
  generatedContent.value = ''
  isOpen.value        = true
}

function close() {
  isOpen.value = false
}

async function generatePost() {
  if (!form.business || !form.industry || !form.audience) {
    error.value = 'Please fill in all required fields.'
    return
  }
  error.value    = ''
  generating.value = true

  try {
    const res  = await fetch(props.generateSingleRoute, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
      credentials: 'same-origin',
      body: JSON.stringify({
        date:          selectedDate.value,
        post_type:     form.postType,
        business_name: form.business,
        industry:      form.industry,
        audience:      form.audience,
        tone:          form.tone,
      }),
    })
    const data = await res.json()
    if (!res.ok || data.error) {
      error.value = data.error ?? 'Generation failed.'
      return
    }
    generatedContent.value = data.content
    scheduledAt.value      = selectedDate.value + 'T' + (data.suggested_time ?? '18:00')
    showResult.value       = true
  } catch (e) {
    error.value = 'A connection error occurred.'
  } finally {
    generating.value = false
  }
}

function regenerate() {
  showResult.value = false
  generatePost()
}

async function savePost() {
  if (!generatedContent.value || !form.page || !scheduledAt.value) {
    saveError.value = 'Please fill in all required fields.'
    return
  }
  saveError.value = ''
  saving.value    = true

  try {
    const res  = await fetch(props.confirmSingleRoute, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
      credentials: 'same-origin',
      body: JSON.stringify({
        page_name:    form.page,
        content:      generatedContent.value,
        scheduled_at: scheduledAt.value,
        post_type:    form.postType,
      }),
    })
    const data = await res.json()
    if (!res.ok || data.error) {
      saveError.value = data.error ?? 'Save failed.'
      return
    }
    emit('post-saved', data.event)
    close()
  } catch (e) {
    saveError.value = 'A connection error occurred.'
  } finally {
    saving.value = false
  }
}

// ─── Expose open() so parent/calendar can call it ────────────────
defineExpose({ open })
</script>

<style scoped>
.dc-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.dc-modal {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

/* Header */
.dc-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.dc-title    { font-size: 17px; font-weight: 800; color: #0f1117; margin: 0; }
.dc-subtitle { font-size: 12px; color: #9ca3af; margin: 0; }
.dc-close-btn {
  color: #9ca3af; border: none; background: #f3f4f6;
  border-radius: 8px; width: 32px; height: 32px;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
}

/* Body */
.dc-body {
  flex: 1; overflow-y: auto;
  padding: 20px 24px;
  display: flex; flex-direction: column; gap: 14px;
}

/* Grid */
.dc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Type grid */
.dc-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ap-tone-btn {
  padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e5e7eb;
  background: #fff; font-size: 13px; font-weight: 600; color: #374151;
  cursor: pointer; transition: all .15s;
}
.ap-tone-btn:hover  { border-color: #7c3aed; color: #7c3aed; }
.ap-tone-btn.active { border-color: #7c3aed; background: #f5f3ff; color: #7c3aed; }

/* Error */
.dc-error {
  background: #fee2e2; color: #991b1b;
  padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 8px;
}

/* Buttons */
.dc-btn-primary {
  width: 100%;
  background: linear-gradient(135deg,#7c3aed,#2563eb);
  color: #fff; font-weight: 700; font-size: 14px;
  padding: 13px; border-radius: 12px; border: none;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.dc-btn-primary:disabled { opacity: .7; cursor: not-allowed; }

.dc-btn-success {
  width: 100%;
  background: linear-gradient(135deg,#10b981,#059669);
  color: #fff; font-weight: 700; font-size: 14px;
  padding: 13px; border-radius: 12px; border: none;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.dc-btn-success:disabled { opacity: .7; cursor: not-allowed; }

/* Result */
.dc-result-header {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 8px;
}
.dc-regen-btn {
  font-size: 12px; color: #7c3aed; background: none; border: none;
  cursor: pointer; font-weight: 600;
}
.dc-char-count { font-size: 11px; color: #9ca3af; margin-bottom: 12px; text-align: right; }

/* Spinner */
.dc-spinner { animation: spin 1s linear infinite; width: 16px; height: 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>