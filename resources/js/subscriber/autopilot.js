/**
 * autopilot.js
 * Handles: AI Content Autopilot modal (4-step wizard)
 *          + Upgrade modal logic.
 *
 * Depends on:
 *   - window.AppConfig.routes
 *   - window.AppConfig.autoShowUpgrade
 *   - window.AppConfig.autoUpgradeReason
 *   - window.calendarInstance  (calendar.js)
 *   - window.showToast()       (post-form.js)
 */

const TYPE_COLORS_AP = {
    educational:  '#8b5cf6',
    promotional:  '#f59e0b',
    entertainment:'#ec4899',
    engagement:   '#06b6d4',
    manual:       '#3b82f6',
};

// ─── Upgrade Modal ────────────────────────────────────────────────────────────
window.openUpgradeModal = function (reason) {
    const modal    = document.getElementById('upgradeModal');
    const title    = document.getElementById('upModal-title');
    const subtitle = document.getElementById('upModal-subtitle');
    const banner   = document.getElementById('upModal-banner');

    const configs = {
        expired: {
            title:    'Your Plan Has Expired',
            subtitle: 'Renew now to continue scheduling posts',
            banner:   '🔒 Your access has ended. Pick a plan below to get back up and running.',
            bannerBg: '#fee2e2', bannerColor: '#991b1b',
        },
        limit: {
            title:    "You've Hit Your Post Limit",
            subtitle: 'Upgrade to schedule more posts this month',
            banner:   "📊 You've used all your posts for this month. Upgrade for more.",
            bannerBg: '#fff3cd', bannerColor: '#856404',
        },
        free_limit: {
            title:    'Unlock Full Power',
            subtitle: 'You need a paid plan to use this feature',
            banner:   '✨ This feature is available on paid plans.',
            bannerBg: '#eff6ff', bannerColor: '#1d4ed8',
        },
        manual: {
            title:    'Upgrade Your Plan',
            subtitle: 'Unlock full access to all features',
            banner:   '', bannerBg: '', bannerColor: '',
        },
    };

    const cfg        = configs[reason] || configs.manual;
    title.textContent    = cfg.title;
    subtitle.textContent = cfg.subtitle;

    if (cfg.banner) {
        banner.textContent      = cfg.banner;
        banner.style.background = cfg.bannerBg;
        banner.style.color      = cfg.bannerColor;
        banner.style.display    = 'flex';
    } else {
        banner.style.display = 'none';
    }

    modal.style.display          = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeUpgradeModal = function () {
    document.getElementById('upgradeModal').style.display = 'none';
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('upgradeModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeUpgradeModal();
    });

    // Auto-open if server flagged it
    const cfg = window.AppConfig ?? {};
    if (cfg.autoShowUpgrade) {
        openUpgradeModal(cfg.autoUpgradeReason);
    }
});

// // ─── Autopilot Modal ──────────────────────────────────────────────────────────
// let apGeneratedPosts = [];
// let apSelectedTone   = 'friendly';

// window.openAutopilotModal = function () {
//     document.getElementById('autopilotModal').classList.replace('hidden', 'flex');
//     showApStep(1);
// };

// window.closeAutopilotModal = function () {
//     document.getElementById('autopilotModal').classList.replace('flex', 'hidden');
// };

// window.showApStep = function (n) {
//     [1, 2, 3, 4].forEach(i => {
//         const el = document.getElementById('apStep' + i);
//         if (!el) return;
//         el.style.display = i === n ? (n === 1 || n === 3 ? 'flex' : 'block') : 'none';
//     });
//     if (n === 1) document.getElementById('apStep1').style.flexDirection = 'column';
//     if (n === 3) document.getElementById('apStep3').style.flexDirection = 'column';
// };

// window.selectTone = function (btn) {
//     document.querySelectorAll('#ap-tone-grid .ap-tone-btn').forEach(b => b.classList.remove('active'));
//     btn.classList.add('active');
//     apSelectedTone = btn.dataset.tone;
// };

// window.startAutopilot = async function () {
//     const business = document.getElementById('ap-business').value.trim();
//     const industry = document.getElementById('ap-industry').value.trim();
//     const audience = document.getElementById('ap-audience').value.trim();
//     const goal     = document.getElementById('ap-goal').value.trim();
//     const page     = document.getElementById('ap-page').value;
//     const ppw      = document.getElementById('ap-ppw').value;
//     const errEl    = document.getElementById('ap-error');

//     if (!business || !industry || !audience || !goal || !page) {
//         errEl.textContent   = 'Please fill in all required fields.';
//         errEl.style.display = 'block';
//         return;
//     }
//     errEl.style.display = 'none';
//     showApStep(2);

//     let prog   = 0;
//     const bar  = document.getElementById('ap-progress-bar');
//     const msgs = [
//         'Analyzing your business...',
//         'Writing educational content...',
//         'Adding promotional posts...',
//         'Arranging dates and times...',
//         'Reviewing the final plan...',
//     ];
//     let msgIdx = 0;

//     const interval = setInterval(() => {
//         prog = Math.min(prog + (prog < 80 ? 2 : 0.3), 92);
//         bar.style.width = prog + '%';
//         if (msgIdx < msgs.length && prog > msgIdx * 18) {
//             document.getElementById('ap-loading-msg').textContent = msgs[msgIdx++];
//         }
//     }, 300);

//     try {
//         const res  = await fetch(window.AppConfig.routes.autopilotGenerate, {
//             method:      'POST',
//             headers:     { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
//             credentials: 'same-origin',
//             body:        JSON.stringify({ business_name: business, industry, audience, goal, page_name: page, posts_per_week: parseInt(ppw), tone: apSelectedTone }),
//         });
//         const data = await res.json();
//         clearInterval(interval);
//         bar.style.width = '100%';

//         if (!res.ok || data.error) {
//             showApStep(1);
//             document.getElementById('ap-error').textContent    = data.error ?? 'Generation failed, please try again.';
//             document.getElementById('ap-error').style.display  = 'block';
//             return;
//         }

//         apGeneratedPosts = data.posts;
//         renderApPreview(data.posts, page);
//         showApStep(3);

//     } catch (e) {
//         clearInterval(interval);
//         showApStep(1);
//         document.getElementById('ap-error').textContent    = 'A connection error occurred.';
//         document.getElementById('ap-error').style.display  = 'block';
//     }
// };

// function renderApPreview(posts, pageName) {
//     const typeEmoji = { educational: '📚', promotional: '🛍️', entertainment: '🎉', engagement: '💬' };

//     document.getElementById('ap-summary').innerHTML =
//         `✅ Generated <strong>${posts.length}</strong> posts for page <strong>${pageName}</strong> — review the content before scheduling.`;

//     const container   = document.getElementById('ap-posts-preview');
//     container.innerHTML = '';

//     posts.forEach(p => {
//         const color = TYPE_COLORS_AP[p.post_type] || '#6b7280';
//         const emoji = typeEmoji[p.post_type]       || '📝';
//         const div   = document.createElement('div');
//         div.style.cssText = 'background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;';
//         div.innerHTML = `
//             <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
//                 <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;background:${color}18;color:${color};">${emoji} ${p.post_type}</span>
//                 <span style="font-size:11px;color:#9ca3af;">${p.scheduled_at?.split(' ')[0] ?? ''} ${p.scheduled_at?.split(' ')[1]?.slice(0,5) ?? ''}</span>
//             </div>
//             <p style="font-size:12px;color:#374151;line-height:1.5;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${p.content}</p>
//         `;
//         container.appendChild(div);
//     });
// }

// window.confirmAutopilot = async function () {
//     const page = document.getElementById('ap-page').value;
//     const btn  = document.getElementById('ap-confirm-btn');
//     btn.disabled  = true;
//     btn.innerHTML = '<svg style="animation:spin 1s linear infinite" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/></svg> Scheduling...';

//     try {
//         const res  = await fetch(window.AppConfig.routes.autopilotConfirm, {
//             method:      'POST',
//             headers:     { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
//             credentials: 'same-origin',
//             body:        JSON.stringify({ page_name: page, posts: apGeneratedPosts }),
//         });
//         const data = await res.json();

//         document.getElementById('ap-success-msg').textContent = data.message ?? 'Scheduled successfully! 🎉';
//         showApStep(4);

//         if (window.calendarInstance && apGeneratedPosts.length) {
//             apGeneratedPosts.forEach(p => {
//                 window.calendarInstance.addEvent({
//                     title:         p.content.slice(0, 25) + '...',
//                     start:         p.scheduled_at,
//                     color:         TYPE_COLORS_AP[p.post_type] || '#3b82f6',
//                     extendedProps: { status: 'pending', page, content: p.content, post_type: p.post_type },
//                 });
//             });
//         }
//     } catch (e) {
//         btn.disabled  = false;
//         btn.innerHTML = 'Schedule All Now';
//         showToast('❌ Save failed, please try again.');
//     }
// };

// document.addEventListener('DOMContentLoaded', () => {
//     document.getElementById('autopilotModal')?.addEventListener('click', e => {
//         if (e.target === e.currentTarget) closeAutopilotModal();
//     });
// });