  
  
      <div class="section">{{ __('OverView')}}</div>
  
  <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;">
                <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div class="stat-num">{{ $stats['total_users'] }}</div>
            <div class="stat-label">{{ __('Total users')}}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f5f3ff;">
                <svg width="20" height="20" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div class="stat-num">{{ $stats['active_paid'] }}</div>
            <div class="stat-label">{{ __('Paid subscribers')}}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-num">${{ number_format($stats['mrr'], 0) }}</div>
            <div class="stat-label">{{ __('MRR this month')}}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;">
                <svg width="20" height="20" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="stat-num">{{ number_format($stats['posts_this_month']) }}</div>
            <div class="stat-label">{{ __('Posts this month')}}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="stat-num">{{ number_format($stats['posts_published']) }}</div>
            <div class="stat-label">{{ __('Published')}}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;">
                <svg width="20" height="20" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-num">{{ number_format($stats['posts_failed']) }}</div>
            <div class="stat-label">{{ __('Failed posts')}}</div>
        </div>

    </div>