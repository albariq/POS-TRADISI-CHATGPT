<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h2>Sales Report ({{ $from }} - {{ $to }})</h2>
    <table>
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->receipt_number }}</td>
                    <td>{{ $sale->created_at }}</td>
                    <td>{{ $sale->customer?->name }}</td>
                    <td>{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
