<div dir="ltr" style="font-family:'Inter',sans-serif;max-width:1100px;margin:32px auto;padding:0 20px;">

   
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#1e293b;margin:0;">
                📊 {{ __('Revenue Anomaly Detector')}}
            </h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">
               {{ __('Upload monthly revenue data · Detect anomalies · Get AI-powered fiscal insights')}}
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">

            <div style="display:flex;align-items:center;gap:6px;">
                @foreach(['3m' => '3M', '6m' => '6M', '1y' => '1Y', 'all' => 'All'] as $key => $label)
                    @php
                        $isActive = match($key) {
                            'all'   => $dateFrom === '' && $dateTo === '',
                            default => false,
                        };
                    @endphp
                    <button
                        wire:click="setQuickRange('{{ $key }}')"
                        style="background:{{ $isActive ? '#2563eb' : '#f1f5f9' }};
                               color:{{ $isActive ? '#fff' : '#374151' }};
                               border:1px solid {{ $isActive ? '#2563eb' : '#e2e8f0' }};
                               border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

     
<div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:6px 12px;">
    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
    </svg>
    <input type="date" wire:model="dateFrom"
        style="border:none;background:none;font-size:13px;color:#374151;outline:none;cursor:pointer;">
    <span style="color:#94a3b8;font-size:12px;">→</span>
    <input type="date" wire:model="dateTo"
        style="border:none;background:none;font-size:13px;color:#374151;outline:none;cursor:pointer;">
    <button wire:click="applyDateRange"
        style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;">
        {{ __('Apply')}}
    </button>
</div>

       
            <label style="cursor:pointer;background:#f1f5f9;border:1px solid #e2e8f0;color:#374151;font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;display:flex;align-items:center;gap:6px;">
                📁 {{ __('Upload CSV')}}
                <input type="file" accept=".csv,.txt" style="display:none" wire:model="csvFile">
            </label>

        </div>
    </div>

    {{-- ── Loading indicator ── --}}
    <div wire:loading wire:target="setQuickRange,uploadCsv,explainWithAI,resetToAll,updatedDateFrom,updatedDateTo"
        style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        {{ __('Updating data')}}...
    </div>


    @if($error)
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:8px;">
        ⚠️ {{ $error }}
    </div>
    @endif

    {{-- ── KPI Cards ── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">

        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;">
            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">{{ __('Total Revenue')}}</div>
            <div style="font-size:26px;font-weight:800;color:#1e293b;">${{ number_format($totalRevenue) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">
                {{ count($rows) }} {{ __('months')}}
                @if($dateFrom && $dateTo)
                    · {{ $dateFrom }} → {{ $dateTo }}
                @endif
            </div>
        </div>

        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;">
            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">{{ __('Monthly Average')}}</div>
            <div style="font-size:26px;font-weight:800;color:#1e293b;">${{ number_format($avgMonthly) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">{{ __('per month')}}</div>
        </div>

        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;">
            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">{{ __('Latest Growth')}}</div>
            <div style="font-size:26px;font-weight:800;color:{{ $growthRate >= 0 ? '#10b981' : '#ef4444' }};">
                {{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">{{ __('vs previous month')}}</div>
        </div>

        <div style="background:{{ count($anomalies) > 0 ? '#fff7ed' : '#f0fdf4' }};border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid {{ count($anomalies) > 0 ? '#fed7aa' : '#bbf7d0' }};">
            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">{{ __('Anomalies')}}</div>
            <div style="font-size:26px;font-weight:800;color:{{ count($anomalies) > 0 ? '#ea580c' : '#16a34a' }};">
                {{ count($anomalies) }}
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">{{ count($anomalies) > 0 ? 'months flagged' : 'all clear' }}</div>
        </div>

    </div>

 
    @if(count($anomalies) > 0)
    <div style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;margin-bottom:24px;">
        <div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:16px;">🚨 {{ __('Flagged Anomalies')}}</div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:2px solid #f1f5f9;">{{ __('Month')}}</th>
                    <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:2px solid #f1f5f9;">{{ __('Revenue')}}</th>
                    <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:2px solid #f1f5f9;">{{ __('Moving Avg')}}</th>
                    <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:2px solid #f1f5f9;">{{ __('Deviation')}}</th>
                    <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:2px solid #f1f5f9;">{{ __('Type')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anomalies as $idx)
                @php
                    $row  = $rows[$idx];
                    $ma   = $movingAvg[$idx];
                    $dev  = $ma > 0 ? round((($row['revenue'] - $ma) / $ma) * 100, 1) : 0;
                    $type = $dev > 0 ? 'spike' : 'drop';
                @endphp
                <tr>
                    <td style="padding:12px 14px;border-bottom:1px solid #f8fafc;font-weight:600;">{{ $row['month'] }}</td>
                    <td style="padding:12px 14px;border-bottom:1px solid #f8fafc;">${{ number_format($row['revenue']) }}</td>
                    <td style="padding:12px 14px;border-bottom:1px solid #f8fafc;color:#64748b;">${{ number_format($ma) }}</td>
                    <td style="padding:12px 14px;border-bottom:1px solid #f8fafc;font-weight:700;color:{{ $dev > 0 ? '#16a34a' : '#dc2626' }};">
                        {{ $dev > 0 ? '+' : '' }}{{ $dev }}%
                    </td>
                    <td style="padding:12px 14px;border-bottom:1px solid #f8fafc;">
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;
                            background:{{ $type === 'spike' ? '#dcfce7' : '#fee2e2' }};
                            color:{{ $type === 'spike' ? '#166534' : '#991b1b' }};">
                            {{ $type === 'spike' ? '↑ Spike' : '↓ Drop' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif


    <div style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
            <div style="font-size:14px;font-weight:700;color:#1e293b;">🤖 {{ __('AI Fiscal Analysis')}}</div>
            <button wire:click="explainWithAI" wire:loading.attr="disabled" wire:target="explainWithAI"
                style="background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-size:13px;font-weight:600;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;display:flex;align-items:center;gap:8px;transition:opacity .2s;"
                wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="explainWithAI">✨ {{ __('Explain with AI')}}</span>
                <span wire:loading wire:target="explainWithAI">⏳ {{ __('Analyzing')}}...</span>
            </button>
        </div>

        @if($aiExplanation)
        <div style="background:#f8fafc;border-left:4px solid #2563eb;padding:16px 20px;border-radius:0 8px 8px 0;font-size:14px;color:#374151;line-height:1.7;">
            {{ __($aiExplanation) }}
        </div>
        @else
        <div style="text-align:center;padding:32px;color:#94a3b8;font-size:14px;">
           {{ __('Click "Explain with AI" to get AI-powered analysis of the selected period.')}}'
        </div>
        @endif
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    let revenueChart = null;

    function buildChart() {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;


        const labels    = JSON.parse(canvas.dataset.labels    || '[]');
        const values    = JSON.parse(canvas.dataset.values    || '[]');
        const ma        = JSON.parse(canvas.dataset.ma        || '[]');
        const anomalies = JSON.parse(canvas.dataset.anomalies || '[]');

        if (!labels.length) return;

        const pointColors = values.map((_, i) => anomalies.includes(i) ? '#ef4444' : 'transparent');
        const pointRadius = values.map((_, i) => anomalies.includes(i) ? 7 : 0);

        if (revenueChart) {
            revenueChart.destroy();
            revenueChart = null;
        }

        revenueChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: values,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,.08)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: pointColors,
                        pointBorderColor: pointColors,
                        pointRadius: pointRadius,
                        pointHoverRadius: 8,
                        borderWidth: 2.5,
                    },
                    {
                        label: 'Moving Average',
                        data: ma,
                        borderColor: '#94a3b8',
                        borderDash: [6, 3],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0,
                        borderWidth: 1.5,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' $' + ctx.parsed.y.toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { callback: v => '$' + (v / 1000).toFixed(0) + 'k' },
                        grid: { color: '#f1f5f9' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }


    document.addEventListener('DOMContentLoaded', buildChart);


    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => requestAnimationFrame(buildChart));
    });


    const modal = document.getElementById('revenueModal');
    if (modal) {
        new MutationObserver(() => {
            if (modal.classList.contains('open')) {
                requestAnimationFrame(buildChart);
            }
        }).observe(modal, { attributes: true, attributeFilter: ['class'] });
    }


    const style = document.createElement('style');
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
})();
</script>