<div class="section-title">{{ __('Revenue & Distribution') }}</div>
<div class="charts-row">

    <div class="chart-card">
        <div class="chart-title">{{ __('Monthly Revenue (last 6 months)') }}</div>
        <canvas id="revenueChart" height="120"></canvas>
    </div>

    <div class="chart-card">
        <div class="chart-title">{{ __('Users by plan') }}</div>
        <canvas id="planChart" height="160"></canvas>
    </div>

</div>

<script>
    const appLocale = "{{ app()->getLocale() }}";
    const locale    = appLocale === 'ar' ? 'ar-SA' : 'en-US';

    // ── Revenue Chart ──────────────────────────────────────────
    const rawLabels = @json($revenueChart->pluck('label'));
    const amounts   = @json($revenueChart->pluck('amount'));

    // ترجمة الأشهر حسب اللغة
const monthMap = {
    'Jan': { 'ar-SA': 'يناير',  'en-US': 'Jan' },
    'Feb': { 'ar-SA': 'فبراير', 'en-US': 'Feb' },
    'Mar': { 'ar-SA': 'مارس',   'en-US': 'Mar' },
    'Apr': { 'ar-SA': 'أبريل',  'en-US': 'Apr' },
    'May': { 'ar-SA': 'مايو',   'en-US': 'May' },
    'Jun': { 'ar-SA': 'يونيو',  'en-US': 'Jun' },
    'Jul': { 'ar-SA': 'يوليو',  'en-US': 'Jul' },
    'Aug': { 'ar-SA': 'أغسطس', 'en-US': 'Aug' },
    'Sep': { 'ar-SA': 'سبتمبر','en-US': 'Sep' },
    'Oct': { 'ar-SA': 'أكتوبر','en-US': 'Oct' },
    'Nov': { 'ar-SA': 'نوفمبر','en-US': 'Nov' },
    'Dec': { 'ar-SA': 'ديسمبر','en-US': 'Dec' },
};

const revenueLabels = rawLabels.map(label => monthMap[label]?.[locale] ?? label);
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: appLocale === 'ar' ? 'الإيرادات' : 'Revenue',
                data: amounts,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `$${ctx.parsed.y.toLocaleString(locale)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => `$${val.toLocaleString(locale)}`
                    }
                }
            }
        }
    });

    // ── Plans Donut Chart ──────────────────────────────────────
    const plansData = @json($plansBreakdown);

    const planLabels = plansData.map(p => `plans.${p.name}`);
    const planCounts = plansData.map(p => p.count);

    new Chart(document.getElementById('planChart'), {
        type: 'doughnut',
        data: {
            labels: planLabels,
            datasets: [{
                data: planCounts,
                backgroundColor: ['#a5b4fc', '#67e8f9', '#6ee7b7', '#fca5a5'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16 }
                }
            }
        }
    });
</script>
