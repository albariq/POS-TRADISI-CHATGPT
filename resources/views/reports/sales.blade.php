@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Sales Report</h1>

<form method="GET" class="flex gap-2 mb-3">
    <input type="date" name="from" value="{{ $from }}" class="border border-slate-300 rounded px-3 py-2" placeholder="From date">
    <input type="date" name="to" value="{{ $to }}" class="border border-slate-300 rounded px-3 py-2" placeholder="To date">
    <button class="bg-slate-900 text-white rounded px-3">Filter</button>
    <a href="{{ route('reports.sales.excel', ['from' => $from, 'to' => $to]) }}" class="bg-slate-200 rounded px-3 py-2 text-sm">Excel</a>
    <a href="{{ route('reports.sales.pdf', ['from' => $from, 'to' => $to]) }}" class="bg-slate-200 rounded px-3 py-2 text-sm">PDF</a>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Net Sales</div>
        <div class="text-lg font-semibold">{{ number_format($summary['net_sales'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">COGS</div>
        <div class="text-lg font-semibold">{{ number_format($summary['cogs'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Gross Profit</div>
        <div class="text-lg font-semibold">{{ number_format($summary['gross_profit'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Gross Margin</div>
        <div class="text-lg font-semibold">{{ number_format($summary['gross_margin'], 2, ',', '.') }}%</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Subtotal</div>
        <div class="text-lg font-semibold">{{ number_format($summary['subtotal'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Discount</div>
        <div class="text-lg font-semibold">{{ number_format($summary['discount'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Tax + Service</div>
        <div class="text-lg font-semibold">{{ number_format($summary['tax'] + $summary['service'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded shadow p-3">
        <div class="text-xs text-slate-500">Grand Total</div>
        <div class="text-lg font-semibold">{{ number_format($summary['grand_total'], 0, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Receipt</th>
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
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $sale->receipt_number }}</td>
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
</div>

<div class="mt-4">{{ $sales->links() }}</div>

<h2 class="text-lg font-semibold mt-8 mb-3">By Product</h2>
<div class="bg-white rounded shadow overflow-x-auto mb-6">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Product</th>
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
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $row->product_name }}</td>
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
</div>

<h2 class="text-lg font-semibold mt-8 mb-3">By Category</h2>
<div class="bg-white rounded shadow overflow-x-auto mb-6">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Category</th>
                <th>Qty</th>
                <th>Net Sales</th>
                <th>COGS</th>
                <th>Gross Profit</th>
                <th>Gross Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCategory as $row)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $row->category_name }}</td>
                    <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<h2 class="text-lg font-semibold mt-8 mb-3">By Cashier</h2>
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Cashier</th>
                <th>Transactions</th>
                <th>Net Sales</th>
                <th>COGS</th>
                <th>Gross Profit</th>
                <th>Gross Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCashier as $row)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $row->cashier_name }}</td>
                    <td>{{ number_format($row->transactions, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                    <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
