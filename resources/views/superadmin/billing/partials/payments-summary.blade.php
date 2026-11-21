<div class="card-header">
    <div class="card-title">Payment Summary</div>
    <span class="badge" style="font-size: 11px; color: var(--text-muted);">{{ $d['period']->label() }}</span>
</div>
<div class="card-body">
    @foreach([
        ['label' => 'Successful Payments', 'value' => $d['successful'], 'color' => 'var(--success)'],
        ['label' => 'Pending Payments', 'value' => $d['pending'], 'color' => 'var(--warning)'],
        ['label' => 'Failed Payments', 'value' => $d['failed'], 'color' => 'var(--danger)'],
        ['label' => 'Refunded Amount', 'value' => $d['refunded'], 'color' => 'var(--purple)'],
        ['label' => 'Net Collected', 'value' => $d['net'], 'color' => 'var(--text-primary)'],
    ] as $row)
    <div class="summary-item">
        <span class="summary-label">{{ $row['label'] }}</span>
        <span class="summary-value" style="color: {{ $row['color'] }}; font-weight: 700;">&#8377; {{ number_format($row['value'], 2) }}</span>
    </div>
    @endforeach
    <div style="margin-top: 12px;"><a href="{{ route('superadmin.reports.payment') }}" class="btn btn-outline-primary btn-sm" style="width: 100%;"><i class="fas fa-chart-bar"></i> View Payment Report</a></div>
</div>
