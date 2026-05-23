<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <div>
            <div class="stat-num">{{ $user->remainingPostsCount() }}</div>
            <div class="stat-label"> {{ __('Remaining Posts') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="22" height="22" fill="#10b981" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </div>
        <div>
            <div class="stat-num">{{ $user->facebookPages->count() }}</div>
            <div class="stat-label">{{ __('Linked Pages') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="stat-num" id="stat-scheduled">—</div>
            <div class="stat-label">{{ __('Scheduled') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="22" height="22" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="stat-num" id="stat-published">—</div>
            <div class="stat-label">{{ __('Published') }}</div>
        </div>
    </div>
</div>