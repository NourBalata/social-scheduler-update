/**
 * calendar.js
 * Handles: FullCalendar init, event click modal, date click modal
 *          (date-click opens "Create Post with AI" modal).
 *
 * Depends on:
 *   - FullCalendar loaded via CDN in _calendar.blade.php
 *   - window.AppConfig.events  (JSON array)
 *   - window.AppConfig.routes  (route URLs)
 *   - window.showToast()       (post-form.js)
 */

const TYPE_COLORS = {
    educational:  '#8b5cf6',
    promotional:  '#f59e0b',
    entertainment:'#ec4899',
    engagement:   '#06b6d4',
    manual:       '#3b82f6',
};

// Populated after FullCalendar renders — used by autopilot.js to add events.
window.calendarInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    const events = window.AppConfig?.events ?? [];

    // ── Stat counters ──────────────────────────────────────────────────────
    const scheduledEl = document.getElementById('stat-scheduled');
    const publishedEl = document.getElementById('stat-published');
    if (scheduledEl) scheduledEl.textContent = events.filter(e => e.extendedProps?.status === 'pending').length;
    if (publishedEl) publishedEl.textContent = events.filter(e => e.extendedProps?.status === 'published').length;

    // ── Calendar ───────────────────────────────────────────────────────────
    const el = document.getElementById('fc-calendar');
    if (!el || typeof FullCalendar === 'undefined') return;

    window.calendarInstance = new FullCalendar.Calendar(el, {
        initialView:   'dayGridMonth',
        height:        'auto',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        buttonText:    { today: 'Today', month: 'Month', listMonth: 'List' },

        events: events.map(e => ({
            ...e,
            color: e.extendedProps?.status === 'published' ? '#10b981'
                 : e.extendedProps?.status === 'failed'    ? '#ef4444'
                 : TYPE_COLORS[e.extendedProps?.post_type] || '#3b82f6',
        })),

        eventClick(info) {
            openCalModal(info.event);
        },

        dateClick(info) {
            openDateClickModal(info.dateStr);
        },

        eventDisplay: 'block',
        dayMaxEvents: 3,
    });

    window.calendarInstance.render();
});

// ─── Cal Detail Modal ─────────────────────────────────────────────────────────
function openCalModal(event) {
    const p        = event.extendedProps;
    const status   = p.status ?? 'pending';
    const colorMap = {
        published: 'background:#d1fae5;color:#065f46',
        failed:    'background:#fee2e2;color:#991b1b',
        pending:   'background:#dbeafe;color:#1e40af',
    };

    const badge     = document.getElementById('cal-badge');
    badge.style.cssText = `font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;${colorMap[status] ?? colorMap.pending}`;
    badge.textContent   = status;

    const typeBadge = document.getElementById('cal-type-badge');
    if (p.post_type && p.post_type !== 'manual') {
        typeBadge.style.cssText = `font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;background:${TYPE_COLORS[p.post_type]}20;color:${TYPE_COLORS[p.post_type]};display:inline-block;`;
        typeBadge.textContent   = p.post_type;
    } else {
        typeBadge.style.display = 'none';
    }

    document.getElementById('cal-time').textContent    = event.start?.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) ?? '';
    document.getElementById('cal-page').textContent    = p.page    ?? '—';
    document.getElementById('cal-content').textContent = p.content ?? '';

    document.getElementById('calModal').classList.replace('hidden', 'flex');
}

window.closeCalModal = function () {
    document.getElementById('calModal').classList.replace('flex', 'hidden');
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('calModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeCalModal();
    });
});

// ─── Date Click Modal ("Create Post with AI") ─────────────────────────────────
let dcSelectedDate = '';
let dcSelectedType = 'educational';

window.openDateClickModal = function (dateStr) {
    dcSelectedDate = dateStr;
    const label    = new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
    document.getElementById('dc-date-label').textContent   = label;
    document.getElementById('dc-scheduled-at').value       = dateStr + 'T18:00';
    document.getElementById('dcFormArea').style.display    = 'block';
    document.getElementById('dcResultArea').style.display  = 'none';
    document.getElementById('dc-error').style.display      = 'none';
    document.getElementById('dateClickModal').classList.replace('hidden', 'flex');
};

window.closeDateClickModal = function () {
    document.getElementById('dateClickModal').classList.replace('flex', 'hidden');
};

window.selectDcType = function (btn) {
    document.querySelectorAll('#dc-type-grid .ap-tone-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    dcSelectedType = btn.dataset.type;
};

window.updateDcCharCount = function (el) {
    const counter = document.getElementById('dc-char-count');
    if (counter) counter.textContent = el.value.length + ' / 2200';
};

window.regenerateDcPost = function () {
    document.getElementById('dcResultArea').style.display = 'none';
    generateDcPost();
};

window.generateDcPost = async function () {
    const business = document.getElementById('dc-business').value.trim();
    const industry = document.getElementById('dc-industry').value.trim();
    const audience = document.getElementById('dc-audience').value.trim();
    const tone     = document.getElementById('dc-tone').value;
    const errEl    = document.getElementById('dc-error');

    if (!business || !industry || !audience) {
        errEl.textContent    = 'Please fill in all required fields.';
        errEl.style.display  = 'block';
        return;
    }
    errEl.style.display = 'none';

    const btn  = document.getElementById('dc-gen-btn');
    const spin = document.getElementById('dc-spin');
    btn.disabled          = true;
    spin.style.display    = 'inline-block';
    btn.childNodes[btn.childNodes.length - 1].textContent = ' Generating...';

    try {
        const res  = await fetch(window.AppConfig.routes.autopilotGenerateSingle, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            credentials: 'same-origin',
            body:        JSON.stringify({ date: dcSelectedDate, post_type: dcSelectedType, business_name: business, industry, audience, tone }),
        });
        const data = await res.json();

        if (!res.ok || data.error) {
            errEl.textContent   = data.error ?? 'Generation failed.';
            errEl.style.display = 'block';
            return;
        }

        document.getElementById('dc-content').value           = data.content;
        updateDcCharCount(document.getElementById('dc-content'));
        document.getElementById('dc-scheduled-at').value      = dcSelectedDate + 'T' + (data.suggested_time ?? '18:00');
        document.getElementById('dcResultArea').style.display = 'block';

    } catch (e) {
        errEl.textContent   = 'A connection error occurred.';
        errEl.style.display = 'block';
    } finally {
        btn.disabled      = false;
        spin.style.display = 'none';
        btn.childNodes[btn.childNodes.length - 1].textContent = ' ✨ Generate Post';
    }
};

window.saveDcPost = async function () {
    const content     = document.getElementById('dc-content').value.trim();
    const page        = document.getElementById('dc-page').value;
    const scheduledAt = document.getElementById('dc-scheduled-at').value;
    const errEl       = document.getElementById('dc-save-error');

    if (!content || !page || !scheduledAt) {
        errEl.textContent   = 'Please fill in all required fields.';
        errEl.style.display = 'block';
        return;
    }
    errEl.style.display = 'none';

    const btn  = document.getElementById('dc-save-btn');
    const spin = document.getElementById('dc-save-spin');
    btn.disabled       = true;
    spin.style.display = 'inline-block';

    try {
        const res  = await fetch(window.AppConfig.routes.autopilotConfirmSingle, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            credentials: 'same-origin',
            body:        JSON.stringify({ page_name: page, content, scheduled_at: scheduledAt, post_type: dcSelectedType }),
        });
        const data = await res.json();

        if (!res.ok || data.error) {
            errEl.textContent   = data.error ?? 'Save failed.';
            errEl.style.display = 'block';
            return;
        }

        if (window.calendarInstance && data.event) {
            window.calendarInstance.addEvent(data.event);
        }
        showToast('✅ Post scheduled successfully!');
        closeDateClickModal();

    } catch (e) {
        errEl.textContent   = 'A connection error occurred.';
        errEl.style.display = 'block';
    } finally {
        btn.disabled       = false;
        spin.style.display = 'none';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('dateClickModal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeDateClickModal();
    });
});