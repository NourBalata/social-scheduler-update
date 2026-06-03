<div class="section-title" style="margin-top:32px;">🔁 {{ __('Auto-Repost') }}</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">{{ __('Active Repost Rules') }}</span>
        <span class="badge badge-blue">2</span>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Page') }}</th>
                    <th>{{ __('Original Post') }}</th>
                    <th>{{ __('Interval') }}</th>
                    <th>{{ __('Next Repost') }}</th>
                    <th>{{ __('Count') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">أ</div>
                            <span style="font-weight:600;">أحمد محمد</span>
                        </div>
                    </td>
                    <td style="color:#6b7280;font-size:12px;">
                        صفحة التقنية البرمجية
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#374151;">
                        هذا النص تجريبي لمعاينة طريقة عرض المنشور الأصلي في الجدول...
                    </td>
                    <td>
                        <span class="badge badge-blue">{{ __('Weekly') }}</span>
                    </td>
                    <td style="font-size:12px;color:#6b7280;">
                        Jun 09, 2026
                    </td>
                    <td style="text-align:center;font-weight:600;">
                        5
                    </td>
                    <td>
                        <span class="badge badge-green">{{ __('Active') }}</span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button onclick="toggleRepostRule(1, false)" class="action-btn" title="{{ __('Pause') }}" style="color:#f59e0b; background: none; border: none; cursor: pointer;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"/>
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                </svg>
                            </button>

                            <button onclick="deleteRepostRule(1)" class="action-btn" title="{{ __('Delete') }}" style="color:#ef4444; background: none; border: none; cursor: pointer;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">M</div>
                            <span style="font-weight:600;">Muhammad Ali</span>
                        </div>
                    </td>
                    <td style="color:#6b7280;font-size:12px;">
                        Marketing Agency
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#374151;">
                        احصل على أفضل العروض والخصومات الحصرية المتاحة الآن...
                    </td>
                    <td>
                        <span class="badge badge-purple">{{ __('Monthly') }}</span>
                    </td>
                    <td style="font-size:12px;color:#6b7280;">
                        Jul 02, 2026
                    </td>
                    <td style="text-align:center;font-weight:600;">
                        12
                    </td>
                    <td>
                        <span class="badge badge-gray">{{ __('Paused') }}</span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button onclick="toggleRepostRule(2, true)" class="action-btn" title="{{ __('Resume') }}" style="color:#10b981; background: none; border: none; cursor: pointer;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                            </button>

                            <button onclick="deleteRepostRule(2)" class="action-btn" title="{{ __('Delete') }}" style="color:#ef4444; background: none; border: none; cursor: pointer;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>