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
    <h3>Summary</h3>
    <table>
        <tbody>
            <tr>
                <th>Net Sales</th>
                <td>{{ number_format($summary['net_sales'], 0, ',', '.') }}</td>
                <th>COGS</th>
                <td>{{ number_format($summary['cogs'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Gross Profit</th>
                <td>{{ number_format($summary['gross_profit'], 0, ',', '.') }}</td>
                <th>Gross Margin</th>
                <td>{{ number_format($summary['gross_margin'], 2, ',', '.') }}%</td>
            </tr>
            <tr>
                <th>Subtotal</th>
                <td>{{ number_format($summary['subtotal'], 0, ',', '.') }}</td>
                <th>Discount</th>
                <td>{{ number_format($summary['discount'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Tax + Service</th>
                <td>{{ number_format($summary['tax'] + $summary['service'], 0, ',', '.') }}</td>
                <th>Grand Total</th>
                <td>{{ number_format($summary['grand_total'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Transactions</h3>
    <table>
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Net Sales</th>
                <th>COGS</th>
                <th>Gross Profit</th>
                <th>Gross Margin</th>
                <th>Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->receipt_number }}</td>
                    <td>{{ $sale->created_at }}</td>
                    <td>{{ $sale->customer?->name }}</td>
                    <td>{{ number_format($sale->net_sales, 0, ',', '.') }}</td>
                    <td>{{ number_format($sale->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ number_format($sale->gross_profit, 0, ',', '.') }}</td>
                    <td>{{ number_format($sale->gross_margin, 2, ',', '.') }}%</td>
                    <td>{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>By Product</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Net Sales</th>
                <th>COGS</th>
                <th>Gross Profit</th>
                <th>Gross Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byProduct as $row)
                <tr>
                    <td>{{ $row->product_name }}</td>
                    <td>{{ $row->category_name }}</td>
                    <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>By Category</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Qty</th>
                <th>Net Sales</th>
                <th>COGS</th>
                <th>Gross Profit</th>
                <th>Gross Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCategory as $row)
                <tr>
                    <td>{{ $row->category_name }}</td>
                    <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>By Cashier</h3>
    <table>
        <thead>
            <tr>
                <th>Cashier</th>
                <th>Transactions</th>
                <th>Net Sales</th>
                <th>COGS</th>
                <th>Gross Profit</th>
                <th>Gross Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCashier as $row)
                <tr>
                    <td>{{ $row->cashier_name }}</td>
                    <td>{{ number_format($row->transactions, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
