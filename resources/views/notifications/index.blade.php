<x-app-layout>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
:root {
    --ink: #0f1117; --mist: #f4f5f7; --steel: #e8eaed;
    --blue: #2563eb; --card: #ffffff; --radius: 16px;
}
* { box-sizing: border-box; }
body { font-family: 'DM Sans', sans-serif; background: #f0f2f5; }
h1,h2,h3 { font-family: 'Syne', sans-serif; }
.wrap { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
.header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.header h1 { font-size: 24px; font-weight: 800; color: var(--ink); }
.btn-primary { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; font-weight: 700; font-size: 13px; padding: 10px 20px; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; }
.notification-card { background: var(--card); border-radius: var(--radius); border: 1px solid var(--steel); padding: 18px 22px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 14px; transition: all .2s; }
.notification-card.unread { border-left: 3px solid var(--blue); background: #f0f7ff; }
.notification-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
.notif-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.notif-icon.comment { background: #eff6ff; }
.notif-icon.reaction { background: #fef3c7; }
.notif-icon.post { background: #f0fdf4; }
.notif-body { flex: 1; }
.notif-title { font-size: 14px; font-weight: 600; color: var(--ink); margin-bottom: 4px; }
.notif-message { font-size: 13px; color: #6b7280; line-height: 1.5; }
.notif-time { font-size: 11px; color: #9ca3af; margin-top: 6px; }
.notif-actions { display: flex; gap: 8px; align-items: center; }
.btn-read { font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; }
.badge { display: inline-flex; align-items: center; justify-content: center; background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
.empty { text-align: center; padding: 64px 24px; color: #9ca3af; }
</style>

<div class="wrap" dir="ltr">
    <div class="header">
        <div style="display:flex;align-items:center;gap:12px;">
            <h1>🔔 Notifications</h1>
            @if($unreadCount > 0)
                <span class="badge">{{ $unreadCount }} new</span>
            @endif
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('dashboard') }}" style="font-size:13px;font-weight:600;color:#6b7280;background:#f3f4f6;border:1px solid #e5e7eb;padding:10px 16px;border-radius:10px;text-decoration:none;">
                ← Back
            </a>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="btn-primary">✓ Mark all read</button>
                </form>
            @endif
        </div>
    </div>

    @if($events->isEmpty())
        <div class="empty">
            <div style="font-size:48px;margin-bottom:12px;">🔕</div>
            <p style="font-size:16px;font-weight:700;">No notifications yet</p>
            <p style="font-size:13px;margin-top:4px;">Notifications will appear here when people interact with your pages</p>
        </div>
    @else
        @foreach($events as $event)
            <div class="notification-card {{ !$event->is_read ? 'unread' : '' }}">
                <div class="notif-icon {{ $event->event_type }}">
                    @if($event->event_type === 'comment') 💬
                    @elseif($event->event_type === 'reaction') ❤️
                    @else 📝
                    @endif
                </div>
                <div class="notif-body">
                    <div class="notif-title">
                        {{ $event->from_name ?? 'Someone' }}
                        @if($event->event_type === 'comment') commented on your post
                        @elseif($event->event_type === 'reaction') reacted to your post
                        @else posted on your page
                        @endif
                    </div>
                    @if($event->message)
                        <div class="notif-message">"{{ Str::limit($event->message, 120) }}"</div>
                    @endif
                    <div class="notif-time">
                        📄 Page ID: {{ $event->page_id }} &nbsp;·&nbsp;
                        🕐 {{ $event->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="notif-actions">
                    @if(!$event->is_read)
                        <form method="POST" action="{{ route('notifications.read', $event->id) }}">
                            @csrf
                            <button type="submit" class="btn-read">Mark read</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach

        <div style="margin-top:20px;">
            {{ $events->links() }}
        </div>
    @endif
</div>
</x-app-layout>