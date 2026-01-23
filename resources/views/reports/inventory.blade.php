@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Inventory Report</h1>

<div class="flex gap-2 mb-3">
    <a href="{{ route('reports.inventory.excel') }}" class="bg-slate-200 rounded px-3 py-2 text-sm">Excel</a>
    <a href="{{ route('reports.inventory.pdf') }}" class="bg-slate-200 rounded px-3 py-2 text-sm">PDF</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Product</th>
                <th>Variant</th>
                <th>Qty (g)</th>
                <th>Min (g)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $stock)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $stock->product?->name }}</td>
                    <td>{{ $stock->variant?->name }}</td>
                    <td>{{ $stock->qty_grams }}</td>
                    <td>{{ $stock->min_qty_grams }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $stocks->links() }}</div>
@endsection
