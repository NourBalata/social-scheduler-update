<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css','resources/css/admin.css','resources/css/subsciber.css','resources/js/app.js','resources/css/subscriber.css','resources/js/INDEX.JS','resources/js/admin/user.js','resources/js/admin/admin.js','resources/js/autopilot.js'])

    @if(app()->getLocale() === 'ar')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif !important; }
        .sidebar { right: 0; left: auto !important; }
        .main-content { margin-right: 260px; margin-left: 0 !important; }
        input, textarea, select { text-align: right; }
        .post-type-tabs { flex-direction: row-reverse; }
        #hashtagSuggestions { direction: rtl; }
        .ai-suggestions { direction: rtl; }
        #captionPicker { direction: rtl; }
        #notifDropdown { right: auto; left: 0; }
        .fc .fc-toolbar { flex-direction: row-reverse; }
        .fc .fc-daygrid-event { direction: rtl; }

        /* كل الـ modals تنقلب RTL */
        #sallaModal > div,
        #upgradeModal > div,
        #payModal > div,
        #templatesModal > div,
        #pageModal > div,
        #autopilotModal > div,
        [id$="Modal"] > div {
            direction: rtl;
            text-align: right;
        }
        [id$="Modal"] > div {
    direction: rtl;
    text-align: right;
}
    </style>
    @endif
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">

                    <div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="flex w-full items-center justify-between px-4 py-6">
                        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ __('Dashboard') }}</h2>

                        <div class="relative" id="notifWrapper">
                            <button onclick="toggleNotifications()" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span id="notifBadge" style="display:none;position:absolute;top:4px;right:4px;min-width:16px;height:16px;background:#ef4444;color:#fff;font-size:10px;font-weight:800;border-radius:999px;align-items:center;justify-content:center;padding:0 4px;"></span>
                            </button>

                            <div id="notifDropdown" style="display:none;position:absolute;{{ app()->getLocale() === 'ar' ? 'right:0;' : 'left:0;' }}top:44px;width:340px;background:#fff;border:1.5px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.12);z-index:999;">
                                <div style="padding:14px 16px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:13px;font-weight:800;color:#111827;">🔔 {{ __('Notifications') }}</span>
                                    <button onclick="markAllRead()" style="font-size:11px;color:#6b7280;background:none;border:none;cursor:pointer;font-weight:600;">{{ __('Mark all as read') }}</button>
                                </div>
                                <div id="notifList" style="max-height:360px;overflow-y:auto;padding:8px 0;">
                                    <div style="text-align:center;color:#9ca3af;font-size:12px;padding:24px;">{{ __('Loading...') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Labels مترجمة تُمرَّر للـ JS --}}
                    <script>
                    const NOTIF_LABELS = {
                        title:   "{{ __('Notifications') }}",
                        markAll: "{{ __('Mark all as read') }}",
                        empty:   "{{ __('No new notifications') }}",
                        loading: "{{ __('Loading...') }}",
                        failed:  "{{ __('Failed to load notifications') }}",
                    };
                    </script>

                </div>
            </div>
        </nav>

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main>
            {{ $slot }}
        </main>
    </div>

    <script>
    const NOTIF_CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let notifOpen = false;

    async function fetchNotifications() {
        try {
            const res  = await fetch('/notifications', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': NOTIF_CSRF },
                credentials: 'same-origin'
            });
            const data = await res.json();
            const badge = document.getElementById('notifBadge');
            const list  = document.getElementById('notifList');

            if (data.count > 0) {
                badge.textContent    = data.count > 9 ? '9+' : data.count;
                badge.style.display  = 'flex';
            } else {
                badge.style.display = 'none';
            }

            if (!data.notifications.length) {
                list.innerHTML = `<div style="text-align:center;color:#9ca3af;font-size:12px;padding:24px;">${NOTIF_LABELS.empty}</div>`;
                return;
            }

            list.innerHTML = data.notifications.map(n => {
                const ok     = n.type === 'published';
                const bg     = ok ? '#f0fdf4' : '#fef2f2';
                const border = ok ? '#bbf7d0' : '#fecaca';
                const icon   = ok ? '✅' : '❌';
                const color  = ok ? '#065f46' : '#991b1b';
                return `<div style="margin:4px 8px;padding:12px 14px;background:${bg};border:1.5px solid ${border};border-radius:10px;">
                    <div style="display:flex;align-items:flex-start;gap:8px;">
                        <span style="font-size:16px;flex-shrink:0;">${icon}</span>
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:12px;font-weight:700;color:${color};margin:0 0 2px;">${n.message}</p>
                            ${n.error ? `<p style="font-size:11px;color:#dc2626;margin:0 0 2px;word-break:break-word;">⚠️ ${n.error}</p>` : ''}
                            <p style="font-size:10px;color:#9ca3af;margin:0;">${n.time}</p>
                        </div>
                    </div>
                </div>`;
            }).join('');

        } catch(e) {
            document.getElementById('notifList').innerHTML =
                `<div style="text-align:center;color:#ef4444;font-size:12px;padding:24px;">${NOTIF_LABELS.failed}</div>`;
        }
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notifDropdown');
        notifOpen = !notifOpen;
        dropdown.style.display = notifOpen ? 'block' : 'none';
        if (notifOpen) fetchNotifications();
    }

    async function markAllRead() {
        await fetch('/notifications/read-all', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': NOTIF_CSRF },
            credentials: 'same-origin'
        });
        document.getElementById('notifBadge').style.display = 'none';
        document.getElementById('notifList').innerHTML =
            `<div style="text-align:center;color:#9ca3af;font-size:12px;padding:24px;">${NOTIF_LABELS.empty}</div>`;
    }

    document.addEventListener('click', e => {
        if (notifOpen && !document.getElementById('notifWrapper')?.contains(e.target)) {
            document.getElementById('notifDropdown').style.display = 'none';
            notifOpen = false;
        }
    });

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
    </script>

</body>
</html>