@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">{{ __('app.inventory') }}</h1>

<form method="POST" action="{{ route('inventory.adjust') }}" class="bg-white rounded shadow p-4 mb-4 space-y-3">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <select name="product_id" class="border border-slate-300 rounded px-3 py-2">
            <option value="" disabled selected>Select product</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
        <select name="type" class="border border-slate-300 rounded px-3 py-2">
            <option value="" disabled selected>Select type</option>
            <option value="in">Stock In</option>
            <option value="out">Stock Out</option>
            <option value="adjust">Adjustment</option>
        </select>
        <input name="qty_grams" type="number" step="0.01" class="border border-slate-300 rounded px-3 py-2" placeholder="Qty (grams)">
    </div>
    <input name="reason" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Reason / Reference">
    <button class="bg-slate-900 text-white rounded px-3 py-2 text-sm">Apply</button>
</form>

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
                    <td class="py-2 px-3">
                        @if ($stock->product)
                            <a href="{{ route('products.show', $stock->product) }}" class="text-blue-600">
                                {{ $stock->product->name }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
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
