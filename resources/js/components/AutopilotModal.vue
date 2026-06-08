<template>
  <!-- Backdrop -->
  <Teleport to="body">
    <div
      v-if="show"
      class="ap-backdrop"
      @click.self="close"
    >
      <div class="ap-modal" :dir="currentDir">

        <!-- ── Header ── -->
        <div class="ap-header">
          <div class="ap-header-left">
            <span class="ap-robot">🤖</span>
            <div>
              <h3 class="ap-title">{{ t('AI Content Autopilot') }}</h3>
              <p class="ap-subtitle">{{ t('Full monthly content plan in one click') }}</p>
            </div>
          </div>
          <button class="ap-close-btn" @click="close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- ── Body ── -->
        <div class="ap-body">

          <!-- Step 1 – Form -->
          <div v-if="step === 1" class="ap-step ap-step-form">

            <div class="ap-grid-2">
              <div>
                <label class="ap-label">{{ t('Business Name') }} *</label>
                <input v-model="form.business" class="ap-input" :placeholder="t('e.g. Al-Asala Restaurant')">
              </div>
              <div>
                <label class="ap-label">{{ t('Industry / Sector') }} *</label>
                <input v-model="form.industry" class="ap-input" :placeholder="t('e.g. Restaurants, Fashion')">
              </div>
              <div>
                <label class="ap-label">{{ t('Target Audience') }} *</label>
                <input v-model="form.audience" class="ap-input" :placeholder="t('e.g. Young adults 18-35')">
              </div>
              <div>
                <label class="ap-label">{{ t('Content Goal') }} *</label>
                <input v-model="form.goal" class="ap-input" :placeholder="t('e.g. Increase sales, grow followers')">
              </div>
              <div>
                <label class="ap-label">{{ t('Page') }} *</label>
                <select v-model="form.page" class="ap-input">
                  <option value="">{{ t('Select a page') }}</option>
                  <option v-for="page in pages" :key="page" :value="page">{{ page }}</option>
                </select>
              </div>
              <div>
                <label class="ap-label">{{ t('Posts per Week') }} *</label>
                <select v-model="form.postsPerWeek" class="ap-input">
                  <option value="3">{{ t('3 posts / week (12/month)') }}</option>
                  <option value="4">{{ t('4 posts / week (16/month)') }}</option>
                  <option value="5">{{ t('5 posts / week (20/month)') }}</option>
                  <option value="7">{{ t('7 posts / week (28/month)') }}</option>
                </select>
              </div>
            </div>

            <!-- Tone selector -->
            <div>
              <label class="ap-label">{{ t('Content Style') }} *</label>
              <div class="ap-tone-grid">
                <button
                  v-for="tone in tones"
                  :key="tone.value"
                  type="button"
                  class="ap-tone-btn"
                  :class="{ active: form.tone === tone.value }"
                  @click="form.tone = tone.value"
                >
                  {{ t(tone.label) }}
                </button>
              </div>
            </div>

            <!-- Error -->
            <div v-if="error" class="ap-error">{{ error }}</div>

            <!-- Generate button -->
            <button class="ap-btn-generate" @click="generate">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              {{ t('Generate Full Month Plan') }}
            </button>
          </div>

          <!-- Step 2 – Loading -->
          <div v-else-if="step === 2" class="ap-step ap-step-loading">
            <svg class="ap-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
            </svg>
            <p class="ap-loading-title">{{ t('AI is generating your plan...') }}</p>
            <p class="ap-loading-msg">{{ loadingMsg }}</p>
            <div class="ap-progress-track">
              <div class="ap-progress-bar" :style="{ width: progress + '%' }"></div>
            </div>
          </div>

          <!-- Step 3 – Preview -->
          <div v-else-if="step === 3" class="ap-step ap-step-preview">
            <div class="ap-summary">
              ✅ {{ t('Generated') }} <strong>{{ generatedPosts.length }}</strong> {{ t('posts for page') }}
              <strong>{{ form.page }}</strong> — {{ t('review before scheduling.') }}
            </div>

            <div class="ap-posts-list">
              <div
                v-for="(post, i) in generatedPosts"
                :key="i"
                class="ap-post-card"
              >
                <div class="ap-post-card-header">
                  <span class="ap-type-badge" :style="typeBadgeStyle(post.post_type)">
                    {{ typeEmoji(post.post_type) }} {{ post.post_type }}
                  </span>
                  <span class="ap-post-date">
                    {{ formatDate(post.scheduled_at) }}
                  </span>
                </div>
                <p class="ap-post-preview">{{ post.content }}</p>
              </div>
            </div>

            <div class="ap-preview-actions">
              <button class="ap-btn-back" @click="step = 1">← {{ t('Regenerate') }}</button>
              <button class="ap-btn-confirm" :disabled="confirming" @click="confirm">
                <svg v-if="!confirming" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <svg v-else class="ap-spinner-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
                </svg>
                {{ confirming ? t('Scheduling...') : t('Schedule All Now') }}
              </button>
            </div>
          </div>

          <!-- Step 4 – Success -->
          <div v-else-if="step === 4" class="ap-step ap-step-success">
            <div class="ap-success-icon">
              <svg width="32" height="32" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <p class="ap-success-title">{{ successMsg }}</p>
            <p class="ap-success-sub">{{ t('All posts are now visible in the calendar below.') }}</p>
            <button class="ap-btn-done" @click="close">{{ t('Got it, view calendar') }}</button>
          </div>

        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  show:   { type: Boolean, default: false },
  pages:  { type: Array,   default: () => [] },
  csrf:   { type: String,  required: true },
  locale: { type: String,  default: 'en' },
  routes: {
    type: Object,
    default: () => ({
      generate: '/autopilot/generate',
      confirm:  '/autopilot/confirm',
    }),
  },
})

// ── Emits ──────────────────────────────────────────────────────────────────
const emit = defineEmits(['update:show', 'scheduled'])

// ── Direction ─────────────────────────────────────────────────────────────
const currentDir = computed(() => props.locale === 'ar' ? 'rtl' : 'ltr')

// ── Translations ──────────────────────────────────────────────────────────
const translations = {
  ar: {
    'AI Content Autopilot':                    'الطيار الآلي لمحتوى الذكاء الاصطناعي',
    'Full monthly content plan in one click':  'خطة محتوى شهرية كاملة بنقرة واحدة',
    'Business Name':                           'اسم النشاط التجاري',
    'Industry / Sector':                       'القطاع / الصناعة',
    'Target Audience':                         'الجمهور المستهدف',
    'Content Goal':                            'هدف المحتوى',
    'Page':                                    'الصفحة',
    'Posts per Week':                          'منشورات في الأسبوع',
    'Content Style':                           'أسلوب المحتوى',
    'Select a page':                           'اختر صفحة',
    'e.g. Al-Asala Restaurant':                'مثال: مطعم الأصالة',
    'e.g. Restaurants, Fashion':               'مثال: مطاعم، أزياء',
    'e.g. Young adults 18-35':                 'مثال: شباب 18-35',
    'e.g. Increase sales, grow followers':     'مثال: زيادة المبيعات، نمو المتابعين',
    '3 posts / week (12/month)':               '3 منشورات / أسبوع (12/شهر)',
    '4 posts / week (16/month)':               '4 منشورات / أسبوع (16/شهر)',
    '5 posts / week (20/month)':               '5 منشورات / أسبوع (20/شهر)',
    '7 posts / week (28/month)':               '7 منشورات / أسبوع (28/شهر)',
    '😊 Friendly & Casual':                    '😊 ودي وعفوي',
    '💼 Formal & Professional':                '💼 رسمي ومهني',
    '😄 Humorous & Light':                     '😄 فكاهي وخفيف',
    '🔥 Inspiring & Motivational':             '🔥 ملهم وتحفيزي',
    'Generate Full Month Plan':                'توليد خطة الشهر الكامل',
    'AI is generating your plan...':           'الذكاء الاصطناعي يولد خطتك...',
    'Generated':                               'تم توليد',
    'posts for page':                          'منشور للصفحة',
    'review before scheduling.':              'راجع المحتوى قبل الجدولة.',
    'Regenerate':                              'إعادة التوليد',
    'Scheduling...':                           'جاري الجدولة...',
    'Schedule All Now':                        'جدولة الكل الآن',
    'All posts are now visible in the calendar below.': 'جميع المنشورات مرئية الآن في التقويم.',
    'Got it, view calendar':                   'حسناً، عرض التقويم',
    'Please fill in all required fields.':     'يرجى ملء جميع الحقول المطلوبة.',
    'Generation failed, please try again.':    'فشل التوليد، يرجى المحاولة مرة أخرى.',
    'A connection error occurred.':            'حدث خطأ في الاتصال.',
    'Analyzing your business...':              'تحليل نشاطك التجاري...',
    'Writing educational content...':          'كتابة المحتوى التعليمي...',
    'Adding promotional posts...':             'إضافة المنشورات الترويجية...',
    'Arranging dates and times...':            'ترتيب التواريخ والأوقات...',
    'Reviewing the final plan...':             'مراجعة الخطة النهائية...',
    'Analyzing your business and crafting the right content': 'تحليل نشاطك التجاري وصياغة المحتوى المناسب',
  }
}

function t(key) {
  if (props.locale === 'ar' && translations.ar[key]) {
    return translations.ar[key]
  }
  return key
}

// ── State ──────────────────────────────────────────────────────────────────
const step         = ref(1)
const error        = ref('')
const confirming   = ref(false)
const successMsg   = ref('')
const loadingMsg   = ref(t('Analyzing your business and crafting the right content'))
const progress     = ref(0)
const generatedPosts = ref([])

const form = reactive({
  business:     '',
  industry:     '',
  audience:     '',
  goal:         '',
  page:         '',
  postsPerWeek: '5',
  tone:         'friendly',
})

// ── Constants ──────────────────────────────────────────────────────────────
const tones = [
  { value: 'friendly',  label: '😊 Friendly & Casual' },
  { value: 'formal',    label: '💼 Formal & Professional' },
  { value: 'humorous',  label: '😄 Humorous & Light' },
  { value: 'inspiring', label: '🔥 Inspiring & Motivational' },
]

const typeColorMap = {
  educational:   '#8b5cf6',
  promotional:   '#f59e0b',
  entertainment: '#ec4899',
  engagement:    '#06b6d4',
}

const typeEmojiMap = {
  educational:   '📚',
  promotional:   '🛍️',
  entertainment: '🎉',
  engagement:    '💬',
}

// ── Helpers ────────────────────────────────────────────────────────────────
function typeEmoji(type)      { return typeEmojiMap[type] ?? '📝' }
function typeBadgeStyle(type) {
  const color = typeColorMap[type] ?? '#6b7280'
  return { background: color + '18', color }
}
function formatDate(dt) {
  if (!dt) return ''
  const [date, time] = dt.split(' ')
  return `${date} ${time?.slice(0, 5) ?? ''}`
}

function startProgressBar() {
  progress.value = 0
  const messages = [
    t('Analyzing your business...'),
    t('Writing educational content...'),
    t('Adding promotional posts...'),
    t('Arranging dates and times...'),
    t('Reviewing the final plan...'),
  ]
  let msgIdx = 0
  const interval = setInterval(() => {
    progress.value = Math.min(progress.value + (progress.value < 80 ? 2 : 0.3), 92)
    if (msgIdx < messages.length && progress.value > msgIdx * 18) {
      loadingMsg.value = messages[msgIdx++]
    }
  }, 300)
  return () => { clearInterval(interval); progress.value = 100 }
}

// ── Actions ────────────────────────────────────────────────────────────────
function close() {
  emit('update:show', false)
  setTimeout(reset, 300)
}

function reset() {
  step.value = 1
  error.value = ''
  generatedPosts.value = []
  progress.value = 0
}

async function generate() {
  const { business, industry, audience, goal, page } = form
  if (!business || !industry || !audience || !goal || !page) {
    error.value = t('Please fill in all required fields.')
    return
  }
  error.value = ''
  step.value  = 2

  const stopProgress = startProgressBar()

  try {
    const res = await fetch(props.routes.generate, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
      body: JSON.stringify({
        business_name:  business,
        industry,
        audience,
        goal,
        page_name:      page,
        posts_per_week: parseInt(form.postsPerWeek),
        tone:           form.tone,
      }),
    })

    const data = await res.json()
    stopProgress()

    if (!res.ok || data.error) {
      step.value  = 1
      error.value = data.error ?? t('Generation failed, please try again.')
      return
    }

    generatedPosts.value = data.posts
    step.value = 3

  } catch {
    stopProgress()
    step.value  = 1
    error.value = t('A connection error occurred.')
  }
}

async function confirm() {
  confirming.value = true
  try {
    const res = await fetch(props.routes.confirm, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrf },
      body: JSON.stringify({ page_name: form.page, posts: generatedPosts.value }),
    })

    const data = await res.json()
    successMsg.value = data.message ?? t('Got it, view calendar')
    step.value = 4
    emit('scheduled', generatedPosts.value)

  } catch {
    // stay on step 3
  } finally {
    confirming.value = false
  }
}
</script>

<style scoped>
.ap-backdrop {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.ap-modal {
  background: #fff; border-radius: 24px;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
  max-width: 560px; width: 100%; max-height: 90vh;
  display: flex; flex-direction: column;
  animation: ap-in .3s cubic-bezier(.34,1.56,.64,1);
  overflow: hidden;
}
@keyframes ap-in {
  from { opacity:0; transform:scale(.92) translateY(16px); }
  to   { opacity:1; transform:scale(1)   translateY(0); }
}
.ap-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:22px 28px; border-bottom:1px solid #f3f4f6; flex-shrink:0;
}
.ap-header-left { display:flex; align-items:center; gap:10px; }
.ap-robot    { font-size:22px; }
.ap-title    { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; color:#0f1117; margin:0; }
.ap-subtitle { font-size:12px; color:#9ca3af; margin:0; }
.ap-close-btn {
  color:#9ca3af; border:none; background:#f3f4f6; border-radius:8px;
  width:32px; height:32px; cursor:pointer;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ap-close-btn:hover { background:#e5e7eb; }
.ap-body { flex:1; overflow-y:auto; }
.ap-step { padding:24px 28px; }
.ap-step-form { display:flex; flex-direction:column; gap:16px; }
.ap-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media (max-width:480px) { .ap-grid-2 { grid-template-columns:1fr; } }
.ap-label {
  display:block; font-size:12px; font-weight:700; color:#374151;
  margin-bottom:5px; text-transform:uppercase; letter-spacing:.04em;
}
.ap-input {
  width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:10px;
  font-size:13px; color:#111827; outline:none; background:#fff;
  box-sizing:border-box; transition:border-color .15s;
}
.ap-input:focus { border-color:#7c3aed; }
.ap-tone-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.ap-tone-btn {
  padding:10px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
  font-size:13px; font-weight:600; color:#6b7280; cursor:pointer;
  background:#fff; transition:all .15s; text-align:center;
}
.ap-tone-btn:hover  { border-color:#7c3aed; color:#7c3aed; }
.ap-tone-btn.active { border-color:#7c3aed; background:#f5f3ff; color:#7c3aed; }
.ap-error { background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:10px; font-size:13px; font-weight:500; }
.ap-btn-generate {
  display:flex; align-items:center; justify-content:center; gap:8px;
  background:linear-gradient(135deg,#7c3aed,#2563eb); color:#fff;
  font-weight:700; font-size:14px; padding:14px; border-radius:12px;
  border:none; cursor:pointer; transition:opacity .15s;
}
.ap-btn-generate:hover { opacity:.88; }
.ap-step-loading { display:flex; flex-direction:column; align-items:center; text-align:center; padding:48px 28px; }
.ap-spinner { width:48px; height:48px; color:#7c3aed; animation:spin 1s linear infinite; margin-bottom:16px; }
@keyframes spin { to { transform:rotate(360deg); } }
.ap-loading-title { font-size:16px; font-weight:700; color:#111827; margin:0 0 8px; }
.ap-loading-msg   { font-size:13px; color:#9ca3af; margin:0 0 20px; }
.ap-progress-track { width:100%; background:#f3f4f6; border-radius:99px; height:6px; overflow:hidden; }
.ap-progress-bar { height:100%; background:linear-gradient(90deg,#7c3aed,#2563eb); border-radius:99px; transition:width .3s; }
.ap-step-preview { display:flex; flex-direction:column; gap:16px; }
.ap-summary { background:#f0fdf4; border:1px solid #a7f3d0; border-radius:12px; padding:14px 18px; font-size:13px; color:#065f46; font-weight:600; }
.ap-posts-list { max-height:320px; overflow-y:auto; display:flex; flex-direction:column; gap:8px; }
.ap-post-card { background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:12px 14px; }
.ap-post-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
.ap-type-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; text-transform:capitalize; }
.ap-post-date { font-size:11px; color:#9ca3af; }
.ap-post-preview { font-size:12px; color:#374151; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
.ap-preview-actions { display:flex; gap:10px; }
.ap-btn-back { flex:1; padding:12px; border:1.5px solid #e5e7eb; border-radius:12px; font-size:13px; font-weight:600; color:#6b7280; cursor:pointer; background:#fff; transition:background .15s; }
.ap-btn-back:hover { background:#f9fafb; }
.ap-btn-confirm {
  flex:2; background:linear-gradient(135deg,#10b981,#059669); color:#fff;
  font-weight:700; font-size:14px; padding:12px; border-radius:12px; border:none;
  cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:opacity .15s;
}
.ap-btn-confirm:hover:not(:disabled) { opacity:.88; }
.ap-btn-confirm:disabled { opacity:.6; cursor:not-allowed; }
.ap-spinner-sm { width:16px; height:16px; animation:spin 1s linear infinite; }
.ap-step-success { display:flex; flex-direction:column; align-items:center; text-align:center; padding:48px 28px; }
.ap-success-icon { width:64px; height:64px; background:#d1fae5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:16px; }
.ap-success-title { font-size:18px; font-weight:800; color:#111827; margin:0 0 8px; }
.ap-success-sub   { font-size:13px; color:#9ca3af; margin:0 0 24px; }
.ap-btn-done { background:#f3f4f6; color:#374151; font-weight:700; font-size:14px; padding:12px 28px; border-radius:12px; border:none; cursor:pointer; transition:background .15s; }
.ap-btn-done:hover { background:#e5e7eb; }
</style>