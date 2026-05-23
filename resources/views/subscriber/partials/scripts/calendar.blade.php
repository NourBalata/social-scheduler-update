<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"></script>

<script>
// ── ثوابت ────────────────────────────────────────────────────────────────────
const typeColors = {
    educational:   '#8b5cf6',
    promotional:   '#f59e0b',
    entertainment: '#ec4899',
    engagement:    '#06b6d4',
    manual:        '#3b82f6',
};

// const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const events = @json($events);

// ── إحصائيات الـ header ───────────────────────────────────────────────────────
document.getElementById('stat-scheduled').textContent = events.filter(e => e.extendedProps?.status === 'pending').length;
document.getElementById('stat-published').textContent = events.filter(e => e.extendedProps?.status === 'published').length;

let calendarInstance = null;
let _currentPostId   = null; // postId اللي فاتح الـ modal حالياً

// ── تهيئة الكليندر بعد تحميل الصفحة كاملة ──────────────────────────────────
window.addEventListener('load', () => {
    calendarInstance = new FullCalendar.Calendar(document.getElementById('fc-calendar'), {
        initialView:   'dayGridMonth',
        height:        'auto',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        buttonText:    { today: 'Today', month: 'Month', listMonth: 'List' },

        editable:  true,
        droppable: true,

        events: events.map(e => ({
            ...e,
            editable: e.extendedProps?.status === 'pending',
            color: e.extendedProps?.status === 'published' ? '#10b981'
                 : e.extendedProps?.status === 'failed'    ? '#ef4444'
                 : typeColors[e.extendedProps?.post_type]  || '#3b82f6',
        })),

        // ── Drag & Drop ──────────────────────────────────────────────────────
        eventDrop(info) {
            const newDate = info.event.start;

            if (newDate < new Date()) {
                info.revert();
                showToast('⚠️ Cannot reschedule to a past date.');
                return;
            }

            fetch(`/posts/${info.event.id}/reschedule`, {
                method:  'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({ scheduled_at: newDate.toISOString() }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Post rescheduled successfully!');
                } else {
                    info.revert();
                    showToast('❌ ' + (data.error ?? 'Failed to reschedule.'));
                }
            })
            .catch(() => { info.revert(); showToast('❌ Connection error.'); });
        },

        // ── Click على منشور ──────────────────────────────────────────────────
        eventClick(info) {
            const p      = info.event.extendedProps;
            const status = p.status ?? 'pending';
            const postId = info.event.id;

            _currentPostId = postId;

            // Status Badge
            const colorMap = {
                published: 'background:#d1fae5;color:#065f46',
                failed:    'background:#fee2e2;color:#991b1b',
                pending:   'background:#dbeafe;color:#1e40af',
            };
            const badge = document.getElementById('cal-badge');
            badge.style.cssText = `font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;${colorMap[status] ?? colorMap.pending}`;
            badge.textContent   = status;

            // Type Badge
            const typeBadge = document.getElementById('cal-type-badge');
            if (p.post_type && p.post_type !== 'manual') {
                typeBadge.style.cssText = `font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;background:${typeColors[p.post_type]}20;color:${typeColors[p.post_type]};display:inline-block;`;
                typeBadge.textContent   = p.post_type;
            } else {
                typeBadge.style.display = 'none';
            }

            // بيانات البوست
            document.getElementById('cal-time').textContent    = info.event.start?.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) ?? '';
            document.getElementById('cal-page').textContent    = p.page ?? '—';
            document.getElementById('cal-content').textContent = p.content ?? '';

            // Retry Section
            const retrySection = document.getElementById('cal-retry-section');
            const analyzing    = document.getElementById('cal-retry-analyzing');
            const retryResult  = document.getElementById('cal-retry-result');

            if (status === 'failed') {
                retrySection.style.display = 'block';
                analyzing.style.display    = 'block';
                retryResult.style.display  = 'none';
                document.getElementById('cal-token-form').style.display = 'none';
                document.getElementById('cal-update-token').value = '';

                document.getElementById('calModal').classList.replace('hidden', 'flex');

                // GET /posts/{post}/retry/analyze
                fetch(`/posts/${postId}/retry/analyze`, {
                    method:  'GET',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                })
                .then(r => r.json())
                .then(data => {
                    analyzing.style.display   = 'none';
                    retryResult.style.display = 'block';

                    document.getElementById('cal-error-text').textContent      = data.error ?? 'Unknown error';
                    document.getElementById('cal-diagnosis-title').textContent = data.diagnosis.title;
                    document.getElementById('cal-diagnosis-msg').textContent   = data.diagnosis.message;

                    const fixBtn       = document.getElementById('cal-fix-btn');
                    fixBtn.textContent = data.diagnosis.action;
                    fixBtn.disabled    = false;
                    fixBtn.onclick     = () => runFix(postId, data.diagnosis.fix);

                    // يُظهر فورم التوكن فقط لو المشكلة token أو permission
                    document.getElementById('cal-token-form').style.display =
                        (data.diagnosis.fix === 'reconnect_page') ? 'block' : 'none';
                })
                .catch(() => {
                    analyzing.style.display   = 'none';
                    retryResult.style.display = 'block';
                    document.getElementById('cal-error-text').textContent      = 'Failed to analyze';
                    document.getElementById('cal-diagnosis-title').textContent = '❓ Unknown Error';
                    document.getElementById('cal-diagnosis-msg').textContent   = 'Could not reach the server.';
                    document.getElementById('cal-token-form').style.display    = 'none';
                });

            } else {
                retrySection.style.display = 'none';
                document.getElementById('calModal').classList.replace('hidden', 'flex');
            }
        },

        dateClick(info) {
            openDateClickModal(info.dateStr);
        },

        eventDisplay: 'block',
        dayMaxEvents: 3,
    });

    calendarInstance.render();
});

// ── تشغيل الـ Fix ─────────────────────────────────────────────────────────────
function runFix(postId, fixType) {
    const fixBtn       = document.getElementById('cal-fix-btn');
    fixBtn.disabled    = true;
    fixBtn.textContent = 'Processing...';

    // POST /posts/{post}/retry/fix
    fetch(`/posts/${postId}/retry/fix`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ fix: fixType }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✅ ' + data.message);
            closeCalModal();
            calendarInstance.refetchEvents();
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showToast('❌ ' + (data.error ?? 'Failed'));
            fixBtn.disabled    = false;
            fixBtn.textContent = 'Retry';
        }
    })
    .catch(() => {
        showToast('❌ Connection error');
        fixBtn.disabled    = false;
        fixBtn.textContent = 'Retry';
    });
}

// ── تحديث التوكن مباشرة ───────────────────────────────────────────────────────
function updatePageDetails() {
    const token   = document.getElementById('cal-update-token').value.trim();
    const saveBtn = document.getElementById('cal-token-save-btn');

    if (!token) {
        showToast('⚠️ Please paste the new Page Access Token');
        return;
    }

    if (!_currentPostId) {
        showToast('❌ Could not identify post. Please close and try again.');
        return;
    }

    saveBtn.disabled    = true;
    saveBtn.textContent = 'Saving...';

    // PATCH /posts/{post}/retry/update-token
    fetch(`/posts/${_currentPostId}/retry/update-token`, {
        method:  'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ page_access_token: token }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✅ ' + data.message);
            closeCalModal();
            calendarInstance.refetchEvents();
        } else {
            showToast('❌ ' + (data.error ?? 'Failed to update token'));
            saveBtn.disabled    = false;
            saveBtn.textContent = '✅ Save & Retry';
        }
    })
    .catch(() => {
        showToast('❌ Connection error');
        saveBtn.disabled    = false;
        saveBtn.textContent = '✅ Save & Retry';
    });
}

// ── إغلاق الـ Modal ───────────────────────────────────────────────────────────
function closeCalModal() {
    document.getElementById('calModal').classList.replace('flex', 'hidden');
    _currentPostId = null;
}

document.getElementById('calModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeCalModal();
});

window.closeCalModal     = closeCalModal;
window.updatePageDetails = updatePageDetails;
window.runFix            = runFix;
</script>