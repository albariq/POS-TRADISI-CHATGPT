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

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Receipt</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $sale->receipt_number }}</td>
                    <td>{{ $sale->created_at }}</td>
                    <td>{{ $sale->customer?->name }}</td>
                    <td>{{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $sales->links() }}</div>
@endsection
