// import { useState } from "react";
// import { createRoot } from "react-dom/client";
// import AutopilotModal from "./components/AutopilotModal";
// import DateClickModal from "./components/DateClickModal";

// function App({ pages, csrf, locale, routes }) {
//   const [showAutopilot, setShowAutopilot] = useState(false);
//   const [showDateClick, setShowDateClick] = useState(false);
//   const [selectedDate,  setSelectedDate]  = useState("");

//   // نفس window.openAutopilotModal اللي كان بالـ Vue
//   window.openAutopilotModal = () => setShowAutopilot(true);

//   // نفس window.openDateClickModal اللي كان بالـ Vanilla JS
//   window.openDateClickModal = (dateStr) => {
//     setSelectedDate(dateStr);
//     setShowDateClick(true);
//   };

//   return (
//     <>
//       <AutopilotModal
//         show={showAutopilot}
//         pages={pages}
//         csrf={csrf}
//         locale={locale}
//         routes={routes}
//         onClose={() => setShowAutopilot(false)}
//         onScheduled={(posts) => {
//           if (!window.calendarInstance) return;
//           const colors = {
//             educational:   "#8b5cf6",
//             promotional:   "#f59e0b",
//             entertainment: "#ec4899",
//             engagement:    "#06b6d4",
//           };
//           posts.forEach((p) => {
//             window.calendarInstance.addEvent({
//               title: p.content.slice(0, 25) + "...",
//               start: p.scheduled_at,
//               color: colors[p.post_type] ?? "#3b82f6",
//               extendedProps: {
//                 status:    "pending",
//                 content:   p.content,
//                 post_type: p.post_type,
//               },
//             });
//           });
//         }}
//       />

//       <DateClickModal
//         show={showDateClick}
//         selectedDate={selectedDate}
//         pages={pages}
//         csrf={csrf}
//         generateSingleRoute={routes.generateSingle}
//         confirmSingleRoute={routes.confirmSingle}
//         onClose={() => setShowDateClick(false)}
//         onPostSaved={(event) => {
//           if (window.calendarInstance && event) {
//             window.calendarInstance.addEvent(event);
//           }
//           window.showToast?.("✅ Post scheduled successfully!");
//         }}
//       />
//     </>
//   );
// }

// // بناخد البيانات من الـ div في الـ HTML
// const container = document.getElementById("react-autopilot-root");

// if (container) {
//   const pages  = JSON.parse(container.dataset.pages  ?? "[]");
//   const csrf   = container.dataset.csrf   ?? "";
//   const locale = container.dataset.locale ?? "en";
//   const routes = {
//     generate:       container.dataset.routeGenerate       ?? "/autopilot/generate",
//     confirm:        container.dataset.routeConfirm        ?? "/autopilot/confirm",
//     generateSingle: container.dataset.routeGenerateSingle ?? "/autopilot/generate-single",
//     confirmSingle:  container.dataset.routeConfirmSingle  ?? "/autopilot/confirm-single",
//   };

//   createRoot(container).render(
//     <App pages={pages} csrf={csrf} locale={locale} routes={routes} />
//   );
// }