<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
   
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

             
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

    
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">

                <div class="relative" id="notifWrapper">
                    <button onclick="toggleNotifications()" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none" id="notifBell">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span id="notifBadge" style="display:none;position:absolute;top:4px;right:4px;min-width:16px;height:16px;background:#ef4444;color:#fff;font-size:10px;font-weight:800;border-radius:999px;display:flex;align-items:center;justify-content:center;padding:0 4px;"></span>
                    </button>

                    <div id="notifDropdown" style="display:none;position:absolute;right:0;top:44px;width:340px;background:#fff;border:1.5px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.12);z-index:999;">
                        <div style="padding:14px 16px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:13px;font-weight:800;color:#111827;">🔔 {{ ('Notifications')}}</span>
                            <button onclick="markAllRead()" style="font-size:11px;color:#6b7280;background:none;border:none;cursor:pointer;font-weight:600;">Mark all as read</button>
                        </div>
                        <div id="notifList" style="max-height:360px;overflow-y:auto;padding:8px 0;">
                            <div style="text-align:center;color:#9ca3af;font-size:12px;padding:24px;">جاري التحميل...</div>
                        </div>
                    </div>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
const NOTIF_CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let notifOpen = false;

async function fetchNotifications() {
    try {
        const res  = await fetch('/notifications', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': NOTIF_CSRF }, credentials: 'same-origin' });
        const data = await res.json();
        const badge = document.getElementById('notifBadge');
        const list  = document.getElementById('notifList');

        // Badge
        if (data.count > 0) {
            badge.textContent = data.count > 9 ? '9+' : data.count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }

        // List
        if (!data.notifications.length) {
            list.innerHTML = '<div style="text-align:center;color:#9ca3af;font-size:12px;padding:24px;">لا توجد إشعارات جديدة</div>';
            return;
        }

        list.innerHTML = data.notifications.map(n => {
            const isPublished = n.type === 'published';
            const bg    = isPublished ? '#f0fdf4' : '#fef2f2';
            const border= isPublished ? '#bbf7d0' : '#fecaca';
            const icon  = isPublished ? '✅' : '❌';
            const color = isPublished ? '#065f46' : '#991b1b';
            return `
            <div style="margin:4px 8px;padding:12px 14px;background:${bg};border:1.5px solid ${border};border-radius:10px;">
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
        document.getElementById('notifList').innerHTML = '<div style="text-align:center;color:#ef4444;font-size:12px;padding:24px;">فشل تحميل الإشعارات</div>';
    }
}

function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    notifOpen = !notifOpen;
    dropdown.style.display = notifOpen ? 'block' : 'none';
    if (notifOpen) fetchNotifications();
}

async function markAllRead() {
    await fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': NOTIF_CSRF }, credentials: 'same-origin' });
    document.getElementById('notifBadge').style.display = 'none';
    document.getElementById('notifList').innerHTML = '<div style="text-align:center;color:#9ca3af;font-size:12px;padding:24px;">لا توجد إشعارات جديدة</div>';
}

// إغلاق عند الضغط خارج الـ dropdown
document.addEventListener('click', e => {
    if (notifOpen && !document.getElementById('notifWrapper').contains(e.target)) {
        document.getElementById('notifDropdown').style.display = 'none';
        notifOpen = false;
    }
});

// Polling كل 30 ثانية
fetchNotifications();
setInterval(fetchNotifications, 30000);
</script>