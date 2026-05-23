{{-- ═══════════════════════════════════════════════════════
     App Config Bridge — Blade → JS
     All PHP variables that JS needs are exposed here
     ═══════════════════════════════════════════════════════ --}}
<script>
window.AppConfig = {
    csrf: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    routes: {
        aiCaption:        '{{ route('ai.caption') }}',
        autopilotGenerate:'{{ route('autopilot.generate') }}',
        autopilotConfirm: '{{ route('autopilot.confirm') }}',
        autopilotSingle:  '{{ route('autopilot.generate.single') }}',
        autopilotConfirmSingle: '{{ route('autopilot.confirm.single') }}',
        mediaIndex:       '/media',
        mediaUpload:      '/media/upload',
        postsStore:       '{{ route('posts.store') }}',
    },
    user: {
        name:  '{{ auth()->user()->name }}',
        email: '{{ auth()->user()->email }}',
        pages: @json($user->facebookPages->map(fn($p) => ['id' => $p->id, 'name' => $p->page_name])),
    },
    autoShowUpgrade: @json($autoShowUpgrade),
    autoUpgradeReason: '{{ $autoUpgradeReason }}',
    events: @json($events),
};
</script>