<x-app-layout>

<x-slot name="header">
    <div style="display:flex;align-items:center;justify-content:space-between;" dir="ltr">
        <span class="brand" style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,#2563eb,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            ⚡ PostFlow
        </span>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="user-chip">
                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</x-slot>

<div style="max-width:860px;margin:40px auto;padding:0 20px;" dir="ltr">

    {{-- ── Back + Title ── --}}
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;">
        <a href="{{ route('dashboard') }}"
            style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#64748b;text-decoration:none;background:#f1f5f9;border:1px solid #e2e8f0;padding:7px 14px;border-radius:8px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Dashboard
        </a>
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#1e293b;margin:0;">Billing History</h1>
            <p style="font-size:13px;color:#64748b;margin:2px 0 0;">All your subscription invoices</p>
        </div>
    </div>

    {{-- ── Manage Subscription ── --}}
    @if(auth()->user()->hasActiveStripeSubscription())
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="font-size:13px;color:#1d4ed8;font-weight:600;">
            💳 You have an active subscription
        </div>
        <form method="POST" action="{{ route('billing.portal') }}">
            @csrf
            <button type="submit"
                style="background:#2563eb;color:#fff;font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;">
                Manage Subscription →
            </button>
        </form>
    </div>
    @endif

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:13px;">
        ✓ {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:13px;">
        ⚠️ {{ session('error') }}
    </div>
    @endif

    {{-- ── Invoice Table ── --}}
    <div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;overflow:hidden;">

        @if($invoices->isEmpty())

        {{-- Empty state --}}
        <div style="text-align:center;padding:64px 20px;">
            <div style="font-size:40px;margin-bottom:12px;">🧾</div>
            <div style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:6px;">No invoices yet</div>
            <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Your billing history will appear here after your first payment.</div>
            <a href="{{ route('plans.index') }}"
                style="display:inline-block;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;font-size:13px;font-weight:600;padding:10px 22px;border-radius:8px;text-decoration:none;">
                View Plans
            </a>
        </div>

        @else

        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f1f5f9;">Date</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f1f5f9;">Plan</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f1f5f9;">Period</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f1f5f9;">Amount</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f1f5f9;">Status</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f1f5f9;">Invoice</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                @php
                    $statusStyles = match($invoice->status) {
                        'paid'          => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => '✓ Paid'],
                        'open'          => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => '⏳ Open'],
                        'void'          => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => '— Void'],
                        'uncollectible' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => '✕ Failed'],
                        default         => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => ucfirst($invoice->status)],
                    };
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;">

                    {{-- Date --}}
                    <td style="padding:14px 20px;color:#374151;font-weight:600;">
                        {{ $invoice->paid_at?->format('M d, Y') ?? $invoice->created_at->format('M d, Y') }}
                    </td>

                    {{-- Plan --}}
                    <td style="padding:14px 20px;color:#374151;">
                        {{ $invoice->plan?->name ?? '—' }}
                    </td>

                    {{-- Period --}}
                    <td style="padding:14px 20px;color:#64748b;">
                        @if($invoice->period_start && $invoice->period_end)
                            {{ $invoice->period_start->format('M d') }} – {{ $invoice->period_end->format('M d, Y') }}
                        @else
                            —
                        @endif
                    </td>

                    {{-- Amount --}}
                    <td style="padding:14px 20px;color:#1e293b;font-weight:700;">
                        ${{ number_format($invoice->amount, 2) }}
                        <span style="font-size:11px;font-weight:400;color:#94a3b8;text-transform:uppercase;">{{ $invoice->currency }}</span>
                    </td>

                    {{-- Status --}}
                    <td style="padding:14px 20px;">
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $statusStyles['bg'] }};color:{{ $statusStyles['color'] }};">
                            {{ $statusStyles['label'] }}
                        </span>
                    </td>

                    {{-- PDF --}}
                    <td style="padding:14px 20px;">
                        @if($invoice->invoice_pdf_url)
                        <a href="{{ $invoice->invoice_pdf_url }}" target="_blank"
                            style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#2563eb;text-decoration:none;background:#eff6ff;border:1px solid #bfdbfe;padding:5px 10px;border-radius:6px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            PDF
                        </a>
                        @else
                        <span style="font-size:12px;color:#cbd5e1;">—</span>
                        @endif
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Pagination ── --}}
        @if($invoices->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div style="font-size:13px;color:#64748b;">
                Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
            </div>
            <div style="display:flex;gap:6px;">
                {{-- Previous --}}
                @if($invoices->onFirstPage())
                <span style="padding:6px 12px;border-radius:6px;font-size:13px;color:#cbd5e1;background:#f8fafc;border:1px solid #f1f5f9;cursor:not-allowed;">← Prev</span>
                @else
                <a href="{{ $invoices->previousPageUrl() }}"
                    style="padding:6px 12px;border-radius:6px;font-size:13px;color:#374151;background:#f8fafc;border:1px solid #e2e8f0;text-decoration:none;">← Prev</a>
                @endif

                {{-- Page numbers --}}
                @foreach($invoices->getUrlRange(1, $invoices->lastPage()) as $page => $url)
                @if($page == $invoices->currentPage())
                <span style="padding:6px 12px;border-radius:6px;font-size:13px;color:#fff;background:#2563eb;border:1px solid #2563eb;font-weight:600;">{{ $page }}</span>
                @else
                <a href="{{ $url }}"
                    style="padding:6px 12px;border-radius:6px;font-size:13px;color:#374151;background:#f8fafc;border:1px solid #e2e8f0;text-decoration:none;">{{ $page }}</a>
                @endif
                @endforeach

                {{-- Next --}}
                @if($invoices->hasMorePages())
                <a href="{{ $invoices->nextPageUrl() }}"
                    style="padding:6px 12px;border-radius:6px;font-size:13px;color:#374151;background:#f8fafc;border:1px solid #e2e8f0;text-decoration:none;">Next →</a>
                @else
                <span style="padding:6px 12px;border-radius:6px;font-size:13px;color:#cbd5e1;background:#f8fafc;border:1px solid #f1f5f9;cursor:not-allowed;">Next →</span>
                @endif
            </div>
        </div>
        @endif

        @endif {{-- end invoices empty check --}}

    </div>

    {{-- ── Summary ── --}}
    @if($invoices->isNotEmpty())
    @php
        $totalPaid = $invoices->getCollection()->where('status', 'paid')->sum('amount');
    @endphp
    <div style="margin-top:16px;text-align:right;font-size:13px;color:#64748b;">
        Total paid (this page): <strong style="color:#1e293b;">${{ number_format($totalPaid, 2) }}</strong>
    </div>
    @endif

</div>

</x-app-layout>