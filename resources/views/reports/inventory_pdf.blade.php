<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h2>Inventory Report</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Variant</th>
                <th>SKU</th>
                <th>Qty (g)</th>
                <th>Min (g)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $stock)
                <tr>
                    <td>{{ $stock->product?->name }}</td>
                    <td>{{ $stock->variant?->name }}</td>
                    <td>{{ $stock->variant?->sku ?? $stock->product?->sku }}</td>
                    <td>{{ $stock->qty_grams }}</td>
                    <td>{{ $stock->min_qty_grams }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
