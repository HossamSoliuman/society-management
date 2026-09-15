<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 10px; color: #374151; margin: 0; padding: 20px; }
        h1 { font-size: 16px; margin: 0 0 4px; color: #E84B1E; }
        .muted { color: #6b7280; }
        table.summary { margin: 10px 0 14px; border-collapse: collapse; }
        table.summary td { padding: 4px 14px 4px 0; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #E84B1E; color: #fff; text-align: left; padding: 6px; font-size: 9px; }
        table.data td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; }
        td.num { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <div class="muted">{{ $society->name }} · {{ $report['period'][0] === $report['period'][1] ? 'As on '.\Carbon\Carbon::parse($report['period'][0])->format('d M Y') : \Carbon\Carbon::parse($report['period'][0])->format('d M Y').' – '.\Carbon\Carbon::parse($report['period'][1])->format('d M Y') }} · Generated {{ now()->format('d M Y H:i') }}</div>

    <table class="summary">
        <tr>
            @foreach($report['summary'] as $label => $value)
                <td><span class="muted">{{ $label }}:</span> <strong>{{ $value }}</strong></td>
            @endforeach
        </tr>
    </table>

    <table class="data">
        <thead><tr>@foreach($report['headings'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse($report['rows'] as $row)
                <tr>@foreach($row as $cell)<td class="{{ is_float($cell) ? 'num' : '' }}">{{ is_float($cell) ? number_format($cell, 2) : $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($report['headings']) }}" class="muted">No rows.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
