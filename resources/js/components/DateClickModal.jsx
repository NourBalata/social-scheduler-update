import { useState, useMemo, useEffect } from "react";
import { createPortal } from "react-dom";


const POST_TYPES = [
  { value: "educational",   label: "📚 Educational" },
  { value: "promotional",   label: "🛍️ Promotional" },
  { value: "entertainment", label: "🎉 Entertainment" },
  { value: "engagement",    label: "💬 Engagement" },
];


const styles = `
.dc-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.6);
  display: flex; align-items: center; justify-content: center;
  z-index: 50; padding: 1rem;
}
.dc-modal {
  background: #fff; border-radius: 24px;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
  max-width: 500px; width: 100%; max-height: 90vh;
  display: flex; flex-direction: column;
}
.dc-header {
  padding: 20px 24px; border-bottom: 1px solid #f3f4f6;
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
}
.dc-title    { font-size: 17px; font-weight: 800; color: #0f1117; margin: 0; }
.dc-subtitle { font-size: 12px; color: #9ca3af; margin: 0; }
.dc-close-btn {
  color: #9ca3af; border: none; background: #f3f4f6;
  border-radius: 8px; width: 32px; height: 32px;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.dc-body {
  flex: 1; overflow-y: auto; padding: 20px 24px;
  display: flex; flex-direction: column; gap: 14px;
}
.dc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.dc-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ap-tone-btn {
  padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e5e7eb;
  background: #fff; font-size: 13px; font-weight: 600; color: #374151;
  cursor: pointer; transition: all .15s;
}
.ap-tone-btn:hover  { border-color: #7c3aed; color: #7c3aed; }
.ap-tone-btn.active { border-color: #7c3aed; background: #f5f3ff; color: #7c3aed; }
.dc-error {
  background: #fee2e2; color: #991b1b;
  padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 8px;
}
.dc-btn-primary {
  width: 100%; background: linear-gradient(135deg,#7c3aed,#2563eb);
  color: #fff; font-weight: 700; font-size: 14px;
  padding: 13px; border-radius: 12px; border: none;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.dc-btn-primary:disabled { opacity: .7; cursor: not-allowed; }
.dc-btn-success {
  width: 100%; background: linear-gradient(135deg,#10b981,#059669);
  color: #fff; font-weight: 700; font-size: 14px;
  padding: 13px; border-radius: 12px; border: none;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.dc-btn-success:disabled { opacity: .7; cursor: not-allowed; }
.dc-result-header {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 8px;
}
.dc-regen-btn {
  font-size: 12px; color: #7c3aed; background: none;
  border: none; cursor: pointer; font-weight: 600;
}
.dc-char-count { font-size: 11px; color: #9ca3af; margin-bottom: 12px; text-align: right; }
.dc-spinner { animation: spin 1s linear infinite; width: 16px; height: 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
`;

// ─── Spinner SVG ─────────────────────────────────────────────────
function Spinner() {
  return (
    <svg className="dc-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <circle cx="12" cy="12" r="10" strokeDasharray="60" strokeDashoffset="20" strokeLinecap="round"/>
    </svg>
  );
}

export default function DateClickModal({
  show,
  selectedDate = "",
  pages = [],
  csrf,
  generateSingleRoute,
  confirmSingleRoute,
  onClose,
  onPostSaved,
}) {


  const [showResult,       setShowResult]       = useState(false);
  const [generating,       setGenerating]       = useState(false);
  const [saving,           setSaving]           = useState(false);
  const [error,            setError]            = useState("");
  const [saveError,        setSaveError]        = useState("");
  const [generatedContent, setGeneratedContent] = useState("");
  const [scheduledAt,      setScheduledAt]      = useState("");

  const [form, setForm] = useState({
    business: "",
    industry: "",
    audience: "",
    tone:     "friendly",
    postType: "educational",
    page:     "",
  });

 
  useEffect(() => {
    if (selectedDate) {
      setScheduledAt(selectedDate + "T18:00");
      setShowResult(false);
      setError("");
      setSaveError("");
      setGeneratedContent("");
    }
  }, [selectedDate]);

  
  const dateLabel = useMemo(() => {
    if (!selectedDate) return "Select a day from the calendar";
    return new Date(selectedDate).toLocaleDateString("en-US", {
      weekday: "long", year: "numeric", month: "long", day: "numeric",
    });
  }, [selectedDate]);


  function updateForm(field, value) {
    setForm(prev => ({ ...prev, [field]: value }));
  }

  function handleClose() {
    onClose?.();
    setShowResult(false);
    setError("");
    setSaveError("");
    setGeneratedContent("");
  }


  async function generatePost() {
    if (!form.business || !form.industry || !form.audience) {
      setError("Please fill in all required fields.");
      return;
    }
    setError("");
    setGenerating(true);
    try {
      const res  = await fetch(generateSingleRoute, {
        method:  "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        credentials: "same-origin",
        body: JSON.stringify({
          date:          selectedDate,
          post_type:     form.postType,
          business_name: form.business,
          industry:      form.industry,
          audience:      form.audience,
          tone:          form.tone,
        }),
      });
      const data = await res.json();
      if (!res.ok || data.error) {
        setError(data.error ?? "Generation failed.");
        return;
      }
      setGeneratedContent(data.content);
      setScheduledAt(selectedDate + "T" + (data.suggested_time ?? "18:00"));
      setShowResult(true);
    } catch {
      setError("A connection error occurred.");
    } finally {
      setGenerating(false);
    }
  }

 
  async function savePost() {
    if (!generatedContent || !form.page || !scheduledAt) {
      setSaveError("Please fill in all required fields.");
      return;
    }
    setSaveError("");
    setSaving(true);
    try {
      const res  = await fetch(confirmSingleRoute, {
        method:  "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
        credentials: "same-origin",
        body: JSON.stringify({
          page_name:    form.page,
          content:      generatedContent,
          scheduled_at: scheduledAt,
          post_type:    form.postType,
        }),
      });
      const data = await res.json();
      if (!res.ok || data.error) {
        setSaveError(data.error ?? "Save failed.");
        return;
      }
      onPostSaved?.(data.event);
      handleClose();
    } catch {
      setSaveError("A connection error occurred.");
    } finally {
      setSaving(false);
    }
  }

  // لو مش ظاهر ما نعرض شي (بدل v-if في Vue)
  if (!show) return null;

  return createPortal(
    <>
      {/* حقن الـ styles مرة وحدة */}
      <style>{styles}</style>

      <div className="dc-overlay" onClick={(e) => e.target === e.currentTarget && handleClose()}>
        <div className="dc-modal" dir="ltr">

          {/* Header */}
          <div className="dc-header">
            <div>
              <h3 className="dc-title">✨ Create Post with AI</h3>
              <p className="dc-subtitle">{dateLabel}</p>
            </div>
            <button className="dc-close-btn" onClick={handleClose}>
              <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          {/* Body */}
          <div className="dc-body">

            {/* Form Area — بدل v-if="!showResult" */}
            {!showResult ? (
              <div>
                <div className="dc-grid-2" style={{ marginBottom: 14 }}>
                  <div>
                    <label className="dash-label">Business Name *</label>
                    <input value={form.business} onChange={e => updateForm("business", e.target.value)}
                      type="text" className="dash-input" placeholder="Al-Asala Restaurant" />
                  </div>
                  <div>
                    <label className="dash-label">Industry *</label>
                    <input value={form.industry} onChange={e => updateForm("industry", e.target.value)}
                      type="text" className="dash-input" placeholder="Restaurants, Fashion..." />
                  </div>
                  <div>
                    <label className="dash-label">Audience *</label>
                    <input value={form.audience} onChange={e => updateForm("audience", e.target.value)}
                      type="text" className="dash-input" placeholder="Young adults 18-35" />
                  </div>
                  <div>
                    <label className="dash-label">Tone *</label>
                    <select value={form.tone} onChange={e => updateForm("tone", e.target.value)} className="dash-input">
                      <option value="friendly">😊 Friendly & Casual</option>
                      <option value="formal">💼 Formal & Professional</option>
                      <option value="humorous">😄 Humorous & Light</option>
                      <option value="inspiring">🔥 Inspiring & Motivational</option>
                    </select>
                  </div>
                </div>

                <div style={{ marginBottom: 14 }}>
                  <label className="dash-label">Post Type *</label>
                  <div className="dc-type-grid">
                    {POST_TYPES.map(t => (
                      <button
                        key={t.value}
                        type="button"
                        className={`ap-tone-btn ${form.postType === t.value ? "active" : ""}`}
                        onClick={() => updateForm("postType", t.value)}
                      >
                        {t.label}
                      </button>
                    ))}
                  </div>
                </div>

                {error && <div className="dc-error">{error}</div>}

                <button className="dc-btn-primary" disabled={generating} onClick={generatePost}>
                  {generating && <Spinner />}
                  {generating ? "Generating..." : "✨ Generate Post"}
                </button>
              </div>

            ) : (
             
              <div>
                <div className="dc-result-header">
                  <label className="dash-label" style={{ margin: 0 }}>Generated Post</label>
                  <button className="dc-regen-btn" onClick={() => { setShowResult(false); generatePost(); }}>
                    ↻ Regenerate
                  </button>
                </div>

                <textarea
                  value={generatedContent}
                  onChange={e => setGeneratedContent(e.target.value)}
                  className="dash-textarea"
                  style={{ minHeight: 140, marginBottom: 12 }}
                />
                <p className="dc-char-count">{generatedContent.length} / 2200</p>

                <div className="dc-grid-2" style={{ marginBottom: 14 }}>
                  <div>
                    <label className="dash-label">Page *</label>
                    <select value={form.page} onChange={e => updateForm("page", e.target.value)} className="dash-input">
                      {pages.map(p => (
                        <option key={p.page_name} value={p.page_name}>{p.page_name}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="dash-label">Publish Time *</label>
                    <input value={scheduledAt} onChange={e => setScheduledAt(e.target.value)}
                      type="datetime-local" className="dash-input" />
                  </div>
                </div>

                {saveError && <div className="dc-error">{saveError}</div>}

                <button className="dc-btn-success" disabled={saving} onClick={savePost}>
                  {!saving ? (
                    <svg width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                  ) : <Spinner />}
                  {saving ? "Scheduling..." : "Schedule This Post"}
                </button>
              </div>
            )}

          </div>
        </div>
      </div>
    </>,
    document.body
  );
}