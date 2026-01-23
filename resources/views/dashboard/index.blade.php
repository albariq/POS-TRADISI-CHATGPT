@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-slate-500">Sales Today</div>
        <div class="text-2xl font-semibold">Rp {{ number_format($todaySales, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-slate-500">Gross Sales</div>
        <div class="text-2xl font-semibold">Rp {{ number_format($grossSales, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-slate-500">Net Sales</div>
        <div class="text-2xl font-semibold">Rp {{ number_format($netSales, 0, ',', '.') }}</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Transactions per Hour</h2>
        <ul class="text-sm text-slate-600 space-y-1">
            @forelse ($transactionsByHour as $row)
                <li>{{ str_pad($row->hour, 2, '0', STR_PAD_LEFT) }}:00 - {{ $row->total }}</li>
            @empty
                <li>No data</li>
            @endforelse
        </ul>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Top Products</h2>
        <ul class="text-sm text-slate-600 space-y-1">
            @forelse ($topProducts as $row)
                <li>{{ $row->name_snapshot }} ({{ $row->qty }})</li>
            @empty
                <li>No data</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="bg-white p-4 rounded shadow mt-6">
    <h2 class="font-semibold mb-2">Low Stock</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-slate-500">
                <tr>
                    <th class="py-2">Product</th>
                    <th>Variant</th>
                    <th>Qty (g)</th>
                    <th>Min (g)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lowStock as $stock)
                    <tr class="border-t">
                        <td class="py-2">{{ $stock->product?->name }}</td>
                        <td>{{ $stock->variant?->name }}</td>
                        <td>{{ $stock->qty_grams }}</td>
                        <td>{{ $stock->min_qty_grams }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-2 text-slate-500">All good</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
