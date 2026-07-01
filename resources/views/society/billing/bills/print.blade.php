<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bill['number'] ?? 'Maintenance Bill' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background: #f1f5f9; padding: 24px; }
        .print-shell { max-width: 800px; margin: 0 auto; background: #fff; padding: 28px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .print-actions { max-width: 800px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 8px; }
        @media print {
            body { background: #fff; padding: 0; }
            .print-actions { display: none; }
            .print-shell { box-shadow: none; border-radius: 0; max-width: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>
    <div class="print-shell">
        @include('society.billing._bill-template', ['design' => $design, 'bill' => $bill])
    </div>
</body>
</html>
