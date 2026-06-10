import "./AutopilotModal.css";
import { useState } from "react";

// ─── الـ Tones اللي بيختار منها المستخدم ─────────────────────────────────
const TONES = [
  { value: "friendly",  label: "😊 Friendly & Casual" },
  { value: "formal",    label: "💼 Formal & Professional" },
  { value: "humorous",  label: "😄 Humorous & Light" },
  { value: "inspiring", label: "🔥 Inspiring & Motivational" },
];

// ─── Step 1: الـ Form ─────────────────────────────────────────────────────
function Step1Form({ form, updateForm, pages, error, onGenerate }) {
  return (
    <div className="ap-step ap-step-form">

      <div className="ap-grid-2">

        <div>
          <label className="ap-label">Business Name *</label>
          <input
            value={form.business}
            onChange={(e) => updateForm("business", e.target.value)}
            className="ap-input"
            placeholder="e.g. Al-Asala Restaurant"
          />
        </div>

        <div>
          <label className="ap-label">Industry / Sector *</label>
          <input
            value={form.industry}
            onChange={(e) => updateForm("industry", e.target.value)}
            className="ap-input"
            placeholder="e.g. Restaurants, Fashion"
          />
        </div>

        <div>
          <label className="ap-label">Target Audience *</label>
          <input
            value={form.audience}
            onChange={(e) => updateForm("audience", e.target.value)}
            className="ap-input"
            placeholder="e.g. Young adults 18-35"
          />
        </div>

        <div>
          <label className="ap-label">Content Goal *</label>
          <input
            value={form.goal}
            onChange={(e) => updateForm("goal", e.target.value)}
            className="ap-input"
            placeholder="e.g. Increase sales, grow followers"
          />
        </div>

        {/* Dropdown - في Vue كان v-for، في React بنستخدم .map() */}
        <div>
          <label className="ap-label">Page *</label>
          <select
            value={form.page}
            onChange={(e) => updateForm("page", e.target.value)}
            className="ap-input"
          >
            <option value="">Select a page</option>
            {pages.map((page) => (
              <option key={page} value={page}>{page}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="ap-label">Posts per Week *</label>
          <select
            value={form.postsPerWeek}
            onChange={(e) => updateForm("postsPerWeek", e.target.value)}
            className="ap-input"
          >
            <option value="3">3 posts / week (12/month)</option>
            <option value="4">4 posts / week (16/month)</option>
            <option value="5">5 posts / week (20/month)</option>
            <option value="7">7 posts / week (28/month)</option>
          </select>
        </div>

      </div>

      {/* Tone Selector */}
      <div>
        <label className="ap-label">Content Style *</label>
        <div className="ap-tone-grid">
          {TONES.map((tone) => (
            <button
              key={tone.value}
              type="button"
              // في Vue كان: :class="{ active: form.tone === tone.value }"
              // في React: template literal عادي
              className={`ap-tone-btn ${form.tone === tone.value ? "active" : ""}`}
              onClick={() => updateForm("tone", tone.value)}
            >
              {tone.label}
            </button>
          ))}
        </div>
      </div>

      {/* Error - في Vue: v-if="error" — في React: && */}
      {error && <div className="ap-error">{error}</div>}

      <button className="ap-btn-generate" onClick={onGenerate}>
        ⚡ Generate Full Month Plan
      </button>

    </div>
  );
}

// ─── Step 2: Loading ──────────────────────────────────────────────────────
function Step2Loading({ progress, loadingMsg }) {
  return (
    <div className="ap-step ap-step-loading">
      <div className="ap-spinner">⏳</div>
      <p className="ap-loading-title">AI is generating your plan...</p>
      <p className="ap-loading-msg">{loadingMsg}</p>
      <div className="ap-progress-track">
        <div className="ap-progress-bar" style={{ width: `${progress}%` }}></div>
      </div>
    </div>
  );
}

// ─── Step 3: Preview ──────────────────────────────────────────────────────
function Step3Preview({ posts, page, onBack, onConfirm, confirming }) {
  return (
    <div className="ap-step ap-step-preview">
      <div className="ap-summary">
        ✅ Generated <strong>{posts.length}</strong> posts for page <strong>{page}</strong>
      </div>
      <div className="ap-posts-list">
        {posts.map((post, i) => (
          <div key={i} className="ap-post-card">
            <div className="ap-post-card-header">
              <span className="ap-type-badge">{post.post_type}</span>
              <span className="ap-post-date">{post.scheduled_at}</span>
            </div>
            <p className="ap-post-preview">{post.content}</p>
          </div>
        ))}
      </div>
      <div className="ap-preview-actions">
        <button className="ap-btn-back" onClick={onBack}>← Regenerate</button>
        <button className="ap-btn-confirm" onClick={onConfirm} disabled={confirming}>
          {confirming ? "Scheduling..." : "Schedule All Now"}
        </button>
      </div>
    </div>
  );
}

// ─── Step 4: Success ──────────────────────────────────────────────────────
function Step4Success({ successMsg, onClose }) {
  return (
    <div className="ap-step ap-step-success">
      <div className="ap-success-icon">✅</div>
      <p className="ap-success-title">{successMsg || "All posts scheduled!"}</p>
      <p className="ap-success-sub">All posts are now visible in the calendar below.</p>
      <button className="ap-btn-done" onClick={onClose}>Got it, view calendar</button>
    </div>
  );
}

// ─── الـ Component الرئيسي ────────────────────────────────────────────────
export default function AutopilotModal({ show, pages = [], csrf, locale = "en", routes, onClose, onScheduled }) {

  const [step, setStep] = useState(1);
  const [error, setError] = useState("");
  const [confirming, setConfirming] = useState(false);
  const [generatedPosts, setGeneratedPosts] = useState([]);
  const [progress, setProgress] = useState(0);
  const [loadingMsg, setLoadingMsg] = useState("Analyzing your business...");
  const [successMsg, setSuccessMsg] = useState("");

  const [form, setForm] = useState({
    business: "",
    industry: "",
    audience: "",
    goal: "",
    page: "",
    postsPerWeek: "5",
    tone: "friendly",
  });

  // بدل v-model — بنحدث حقل واحد بالـ form
  function updateForm(field, value) {
    setForm((prev) => ({ ...prev, [field]: value }));
  }

  function handleClose() {
    onClose?.();
    setTimeout(() => {
      setStep(1);
      setError("");
      setGeneratedPosts([]);
      setProgress(0);
    }, 300);
  }

  // Progress bar مثل Vue بالضبط
  function startProgressBar() {
    setProgress(0);
    const messages = [
      "Analyzing your business...",
      "Writing educational content...",
      "Adding promotional posts...",
      "Arranging dates and times...",
      "Reviewing the final plan...",
    ];
    let msgIdx = 0;
    let currentProgress = 0;

    const interval = setInterval(() => {
      currentProgress = Math.min(currentProgress + (currentProgress < 80 ? 2 : 0.3), 92);
      setProgress(currentProgress);
      if (msgIdx < messages.length && currentProgress > msgIdx * 18) {
        setLoadingMsg(messages[msgIdx++]);
      }
    }, 300);

    return () => { clearInterval(interval); setProgress(100); };
  }

  async function generate() {
    const { business, industry, audience, goal, page } = form;
    if (!business || !industry || !audience || !goal || !page) {
      setError("Please fill in all required fields.");
      return;
    }
    setError("");
    setStep(2);

    const stopProgress = startProgressBar();

    try {
      const res = await fetch(routes?.generate ?? "/autopilot/generate", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        body: JSON.stringify({
          business_name:  business,
          industry,
          audience,
          goal,
          page_name:      page,
          posts_per_week: parseInt(form.postsPerWeek),
          tone:           form.tone,
        }),
      });

      const data = await res.json();
      stopProgress();

      if (!res.ok || data.error) {
        setStep(1);
        setError(data.error ?? "Generation failed, please try again.");
        return;
      }

      setGeneratedPosts(data.posts);
      setStep(3);

    } catch {
      stopProgress();
      setStep(1);
      setError("A connection error occurred.");
    }
  }

  async function confirm() {
    setConfirming(true);
    try {
      const res = await fetch(routes?.confirm ?? "/autopilot/confirm", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        body: JSON.stringify({ page_name: form.page, posts: generatedPosts }),
      });

      const data = await res.json();
      setSuccessMsg(data.message ?? "All posts scheduled!");
      setStep(4);
      onScheduled?.(generatedPosts);

    } catch {
      // نبقى على step 3
    } finally {
      setConfirming(false);
    }
  }

  // لو مش ظاهر ما نعرض شي — بدل v-if على الـ backdrop
  if (!show) return null;

  const dir = locale === "ar" ? "rtl" : "ltr";

  return (
    <div className="ap-backdrop" onClick={(e) => e.target === e.currentTarget && handleClose()}>
      <div className="ap-modal" dir={dir}>

        {/* Header */}
        <div className="ap-header">
          <div className="ap-header-left">
            <span className="ap-robot">🤖</span>
            <div>
              <h3 className="ap-title">AI Content Autopilot</h3>
              <p className="ap-subtitle">Full monthly content plan in one click</p>
            </div>
          </div>
          <button className="ap-close-btn" onClick={handleClose}>✕</button>
        </div>

        {/* Body - بنعرض الـ step الصح */}
        <div className="ap-body">
          {step === 1 && (
            <Step1Form
              form={form}
              updateForm={updateForm}
              pages={pages}
              error={error}
              onGenerate={generate}
            />
          )}
          {step === 2 && (
            <Step2Loading progress={progress} loadingMsg={loadingMsg} />
          )}
          {step === 3 && (
            <Step3Preview
              posts={generatedPosts}
              page={form.page}
              onBack={() => setStep(1)}
              onConfirm={confirm}
              confirming={confirming}
            />
          )}
          {step === 4 && (
            <Step4Success successMsg={successMsg} onClose={handleClose} />
          )}
        </div>

      </div>
    </div>
  );
}