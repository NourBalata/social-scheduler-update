@php
    use Carbon\Carbon;

    $userId = $user->id;

    // ── Daily (آخر 30 يوم) ──────────────────────────────────────────
    $dailyRaw = \App\Models\ScheduledPost::where('user_id', $userId)
        ->where('scheduled_at', '>=', now()->subDays(29)->startOfDay())
        ->selectRaw("DATE(scheduled_at) as day, status, COUNT(*) as total")
        ->groupBy('day', 'status')
        ->orderBy('day')
        ->get();

    $dailyDates = collect(range(0, 29))->map(fn($i) => now()->subDays(29 - $i)->format('Y-m-d'));

    $dailyPublished = $dailyDates->map(fn($d) =>
        $dailyRaw->where('day', $d)->where('status', 'published')->sum('total')
    );
    $dailyFailed = $dailyDates->map(fn($d) =>
        $dailyRaw->where('day', $d)->where('status', 'failed')->sum('total')
    );
    $dailyPending = $dailyDates->map(fn($d) =>
        $dailyRaw->where('day', $d)->where('status', 'pending')->sum('total')
    );
    $dailyLabels = $dailyDates->map(fn($d) => Carbon::parse($d)->format('M d'));

    // ── Weekly (آخر 12 أسبوع) ───────────────────────────────────────
    $weeklyRaw = \App\Models\ScheduledPost::where('user_id', $userId)
        ->where('scheduled_at', '>=', now()->subWeeks(11)->startOfWeek())
        ->selectRaw("YEARWEEK(scheduled_at, 1) as yw, status, COUNT(*) as total")
        ->groupBy('yw', 'status')
        ->orderBy('yw')
        ->get();

    $weeklyKeys = collect(range(0, 11))->map(fn($i) =>
        now()->subWeeks(11 - $i)->startOfWeek()->format('oW') // ISO year+week
    );
    $weeklyLabels = collect(range(0, 11))->map(fn($i) =>
        'W' . now()->subWeeks(11 - $i)->startOfWeek()->format('W')
    );

    $weeklyPublished = $weeklyKeys->map(fn($yw) =>
        $weeklyRaw->where('yw', (int)$yw)->where('status', 'published')->sum('total')
    );
    $weeklyFailed = $weeklyKeys->map(fn($yw) =>
        $weeklyRaw->where('yw', (int)$yw)->where('status', 'failed')->sum('total')
    );

    // ── Summary numbers ─────────────────────────────────────────────
    $totalPublished = $dailyPublished->sum();
    $totalFailed    = $dailyFailed->sum();
    $totalPending   = $dailyPending->sum();
    $bestDay = $dailyDates->map(fn($d) =>
        $dailyRaw->where('day', $d)->sum('total')
    )->max();
@endphp

<div style="background:#fff;border-radius:20px;border:1px solid #e5e7eb;padding:24px;margin-bottom:20px;" dir="ltr">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <p style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin:0;">Activity</p>
            <h3 style="font-size:18px;font-weight:800;color:#111827;margin:4px 0 0;font-family:'Syne',sans-serif;">Post Analytics</h3>
        </div>
        <div style="display:flex;gap:8px;">
            <button onclick="switchChart('daily')" id="btn-daily"
                style="padding:7px 16px;border-radius:99px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#111827;color:#fff;transition:all .2s;">
                Daily
            </button>
            <button onclick="switchChart('weekly')" id="btn-weekly"
                style="padding:7px 16px;border-radius:99px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#f3f4f6;color:#6b7280;transition:all .2s;">
                Weekly
            </button>
        </div>
    </div>

    {{-- Mini stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        <div style="background:#f0fdf4;border-radius:12px;padding:14px 16px;">
            <div style="font-size:22px;font-weight:800;color:#10b981;">{{ $totalPublished }}</div>
            <div style="font-size:11px;font-weight:600;color:#6b7280;margin-top:2px;">Published</div>
        </div>
        <div style="background:#fef2f2;border-radius:12px;padding:14px 16px;">
            <div style="font-size:22px;font-weight:800;color:#ef4444;">{{ $totalFailed }}</div>
            <div style="font-size:11px;font-weight:600;color:#6b7280;margin-top:2px;">Failed</div>
        </div>
        <div style="background:#eff6ff;border-radius:12px;padding:14px 16px;">
            <div style="font-size:22px;font-weight:800;color:#3b82f6;">{{ $totalPending }}</div>
            <div style="font-size:11px;font-weight:600;color:#6b7280;margin-top:2px;">Scheduled</div>
        </div>
    </div>

    {{-- Chart --}}
    <div style="position:relative;height:220px;">
        <canvas id="analyticsChart"></canvas>
    </div>


    {{-- Download Button --}}
    <div style="margin-top:16px;display:flex;justify-content:flex-end;">
        <a href="{{ route('reports.monthly', ['month' => now()->month, 'year' => now()->year]) }}"
           style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#111827;color:#fff;font-size:12px;font-weight:700;border-radius:10px;text-decoration:none;"
           onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download PDF Report
        </a>
    </div>

    {{-- Legend --}}
    <div style="display:flex;gap:16px;margin-top:14px;flex-wrap:wrap;">
        <span style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280;font-weight:600;">
            <span style="width:10px;height:10px;border-radius:3px;background:#10b981;display:inline-block;"></span> Published
        </span>
        <span style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280;font-weight:600;">
            <span style="width:10px;height:10px;border-radius:3px;background:#ef4444;display:inline-block;"></span> Failed
        </span>
        <span style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280;font-weight:600;">
            <span style="width:10px;height:10px;border-radius:3px;background:#bfdbfe;display:inline-block;"></span> Pending
        </span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dailyData = {
    labels:    @json($dailyLabels->values()),
    published: @json($dailyPublished->values()),
    failed:    @json($dailyFailed->values()),
    pending:   @json($dailyPending->values()),
};
const weeklyData = {
    labels:    @json($weeklyLabels->values()),
    published: @json($weeklyPublished->values()),
    failed:    @json($weeklyFailed->values()),
};

const ctx = document.getElementById('analyticsChart').getContext('2d');

const chartConfig = (data, showPending) => ({
    type: 'bar',
    data: {
        labels: data.labels,
        datasets: [
            {
                label: 'Published',
                data: data.published,
                backgroundColor: '#10b981',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Failed',
                data: data.failed,
                backgroundColor: '#ef4444',
                borderRadius: 6,
                borderSkipped: false,
            },
            ...(showPending ? [{
                label: 'Pending',
                data: data.pending,
                backgroundColor: '#bfdbfe',
                borderRadius: 6,
                borderSkipped: false,
            }] : []),
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
        scales: {
            x: {
                stacked: false,
                grid: { display: false },
                ticks: { font: { size: 10 }, maxTicksLimit: 10, color: '#9ca3af' },
            },
            y: {
                beginAtZero: true,
                grid: { color: '#f3f4f6' },
                ticks: { stepSize: 1, color: '#9ca3af', font: { size: 11 } },
            },
        },
    },
});

let chart = new Chart(ctx, chartConfig(dailyData, true));
let current = 'daily';

function switchChart(type) {
    if (current === type) return;
    current = type;
    chart.destroy();
    chart = new Chart(ctx, type === 'daily'
        ? chartConfig(dailyData, true)
        : chartConfig(weeklyData, false)
    );
    document.getElementById('btn-daily').style.background  = type === 'daily'  ? '#111827' : '#f3f4f6';
    document.getElementById('btn-daily').style.color       = type === 'daily'  ? '#fff'    : '#6b7280';
    document.getElementById('btn-weekly').style.background = type === 'weekly' ? '#111827' : '#f3f4f6';
    document.getElementById('btn-weekly').style.color      = type === 'weekly' ? '#fff'    : '#6b7280';
}
</script>