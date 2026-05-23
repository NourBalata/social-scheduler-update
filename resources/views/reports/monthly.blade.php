<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; padding: 32px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .header-title { font-size: 22px; font-weight: 800; color: #111827; }
        .header-sub   { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .header-badge { background: #111827; color: #fff; font-size: 11px; font-weight: 700; padding: 6px 14px; border-radius: 8px; }

        /* Stats */
        .stats { display: table; width: 100%; margin-bottom: 28px; border-spacing: 10px; }
        .stat-card { display: table-cell; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 14px 18px; text-align: center; }
        .stat-number { font-size: 28px; font-weight: 800; color: #111827; }
        .stat-label  { font-size: 11px; color: #6b7280; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: .05em; }
        .stat-card.published .stat-number { color: #059669; }
        .stat-card.failed    .stat-number { color: #dc2626; }
        .stat-card.pending   .stat-number { color: #d97706; }

        /* Table */
        .section-title { font-size: 13px; font-weight: 800; color: #111827; margin-bottom: 10px; text-transform: uppercase; letter-spacing: .06em; }
        table  { width: 100%; border-collapse: collapse; }
        thead  { background: #f3f4f6; }
        th     { padding: 9px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1.5px solid #e5e7eb; }
        td     { padding: 9px 12px; font-size: 11px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tr:last-child td { border-bottom: none; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge.published { background: #d1fae5; color: #065f46; }
        .badge.pending   { background: #fef3c7; color: #92400e; }
        .badge.failed    { background: #fee2e2; color: #991b1b; }

        .content-cell { max-width: 260px; overflow: hidden; }
        .content-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px; }

        .empty { text-align: center; color: #9ca3af; padding: 32px; font-size: 12px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1.5px solid #e5e7eb; display: flex; justify-content: space-between; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="header-title">📊 Monthly Report</div>
            <div class="header-sub">{{ $monthName }} · {{ $user->name }}</div>
        </div>
        <div class="header-badge">PostFlow</div>
    </div>

    {{-- Stats --}}
    <div class="stats">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Posts</div>
        </div>
        <div class="stat-card published">
            <div class="stat-number">{{ $stats['published'] }}</div>
            <div class="stat-label">Published</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-number">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card failed">
            <div class="stat-number">{{ $stats['failed'] }}</div>
            <div class="stat-label">Failed</div>
        </div>
    </div>

    {{-- Posts Table --}}
    <div class="section-title">Post Details</div>

    @if($posts->isEmpty())
        <div class="empty">No posts scheduled for this month.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Page</th>
                    <th>Content</th>
                    <th>Scheduled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $i => $post)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $post->facebookPage?->page_name ?? '—' }}</td>
                    <td class="content-cell">
                        <div class="content-text">{{ $post->content }}</div>
                    </td>
                    <td>{{ $post->scheduled_at->format('M j, g:i A') }}</td>
                    <td><span class="badge {{ $post->status }}">{{ $post->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>Generated {{ now()->format('M j, Y · g:i A') }}</span>
        <span>{{ $user->email }}</span>
    </div>

</body>
</html>