import { useState } from "react";
import { createRoot } from "react-dom/client";
import AutopilotModal from "./components/AutopilotModal";

// ─── هاد الملف هو الـ entry point للـ React ──────────────────────────────
// بيشبه vue-app.js بالضبط — بس بـ React

function App() {
  const [show, setShow] = useState(false);

  // نفس window.openAutopilotModal اللي كان بالـ Vue
  window.openAutopilotModal = () => setShow(true);

  // بناخد البيانات من الـ HTML مثل ما كان Vue يعمل
  const el = document.getElementById("react-autopilot-root");
  const pages = JSON.parse(el?.dataset.pages ?? "[]");
  const csrf  = el?.dataset.csrf ?? "";
  const locale = el?.dataset.locale ?? "en";

  return (
    <AutopilotModal
      show={show}
      pages={pages}
      csrf={csrf}
      locale={locale}
      onClose={() => setShow(false)}
      onScheduled={(posts) => {
        // نفس emit('scheduled') اللي كان بالـ Vue
        if (!window.calendarInstance) return;
        const colors = {
          educational:   "#8b5cf6",
          promotional:   "#f59e0b",
          entertainment: "#ec4899",
          engagement:    "#06b6d4",
        };
        posts.forEach((p) => {
          window.calendarInstance.addEvent({
            title: p.content.slice(0, 25) + "...",
            start: p.scheduled_at,
            color: colors[p.post_type] ?? "#3b82f6",
            extendedProps: {
              status: "pending",
              content: p.content,
              post_type: p.post_type,
            },
          });
        });
      }}
    />
  );
}

// بنشغل React على div معين بالصفحة
const container = document.getElementById("react-autopilot-root");
if (container) {
  createRoot(container).render(<App />);
}