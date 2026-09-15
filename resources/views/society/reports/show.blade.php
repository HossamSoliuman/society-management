@extends('society.layouts.app')

@section('title', $data['title'])

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $data['title'] }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Reports</span>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $data['title'] }}</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 8px;">
            <a href="{{ route('society.reports.show', ['report' => $report] + request()->query() + ['export' => 'xlsx']) }}" class="btn btn-secondary" style="color: var(--success); border-color: var(--success);"><i class="fas fa-file-excel"></i> Excel</a>
            <a href="{{ route('society.reports.show', ['report' => $report] + request()->query() + ['export' => 'pdf']) }}" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> PDF</a>
        </div>
    </div>
</div>

<div class="tabs">
    @foreach(['collection' => 'Collection Report', 'expense' => 'Expense Report', 'defaulter' => 'Defaulter Report'] as $key => $label)
        <a href="{{ route('society.reports.show', $key) }}" class="tab {{ $report === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.reports.show', $report) }}" style="display: flex; gap: 12px; align-items: end; flex-wrap: wrap;">
            @if($report !== 'defaulter')
                <div class="form-group" style="margin-bottom: 0;"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
            @endif
            <div class="form-group" style="margin-bottom: 0;"><label class="form-label">{{ $report === 'defaulter' ? 'As on' : 'To' }}</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
            @if($report === 'collection')
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Mode</label>
                    <select name="mode" class="form-control">
                        <option value="">All</option>
                        @foreach(['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'net_banking' => 'Net Banking', 'cheque' => 'Cheque', 'other' => 'Other'] as $v => $l)
                            <option value="{{ $v }}" {{ request('mode') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($report === 'expense')
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tower</label>
                    <select name="tower" class="form-control">
                        <option value="">All</option>
                        @foreach($towers as $tower)
                            <option value="{{ $tower }}" {{ request('tower') === $tower ? 'selected' : '' }}>{{ $tower }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;"><label class="form-label">Min days overdue</label><input type="number" min="0" name="min_days" class="form-control" value="{{ request('min_days', 0) }}" style="width: 120px;"></div>
            @endif
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Apply</button>
        </form>
    </div>
</div>

<div class="stats-grid stats-grid-{{ count($data['summary']) }}">
    @foreach($data['summary'] as $label => $value)
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value" style="font-size: 18px;">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="content-grid" style="grid-template-columns: {{ $data['breakdown'] ? '1fr 300px' : '1fr' }};">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr>@foreach($data['headings'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
                    <tbody>
                        @forelse($data['rows'] as $row)
                            <tr>@foreach($row as $cell)<td style="{{ is_float($cell) ? 'text-align: right;' : '' }}">{{ is_float($cell) ? number_format($cell, 2) : $cell }}</td>@endforeach</tr>
                        @empty
                            <tr><td colspan="{{ count($data['headings']) }}"><div class="empty-state"><div class="empty-state-title">No rows for this period</div></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($data['breakdown'])
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 15px;">Breakdown</div>
                @php($max = max(1, collect($data['breakdown'])->max('value')))
                @foreach($data['breakdown'] as $item)
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                            <span style="color: var(--text-secondary);">{{ $item['label'] }}</span>
                            <span style="font-weight: 600;">{{ format_inr($item['value']) }}</span>
                        </div>
                        <div class="progress-bar"><div class="progress-bar-fill" style="width: {{ (int) round($item['value'] / $max * 100) }}%; background: var(--primary);"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
