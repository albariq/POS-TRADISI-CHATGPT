@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Product Detail</h1>
    <a href="{{ route('products.edit', $product) }}" class="text-sm text-blue-600">Edit</a>
</div>

<div class="bg-white rounded shadow p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-slate-500">Name</div>
            <div class="font-semibold">{{ $product->name }}</div>
        </div>
        <div>
            <div class="text-slate-500">SKU</div>
            <div class="font-semibold">{{ $product->sku }}</div>
        </div>
        <div>
            <div class="text-slate-500">Category</div>
            <div class="font-semibold">{{ $product->category?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-slate-500">Status</div>
            <div class="font-semibold">{{ $product->is_active ? 'Active' : 'Inactive' }}</div>
        </div>
        <div>
            <div class="text-slate-500">Barcode</div>
            <div class="font-semibold">{{ $product->barcode ?? '-' }}</div>
        </div>
        <div>
            <div class="text-slate-500">Base Price</div>
            <div class="font-semibold">Rp {{ number_format($product->base_price ?? 0, 0, ',', '.') }}</div>
        </div>
        <div>
            <div class="text-slate-500">Description</div>
            <div class="font-semibold">{{ $product->description ?: '-' }}</div>
        </div>
        <div>
            <div class="text-slate-500">Tags</div>
            <div class="font-semibold">
                @if ($product->tags->count())
                    {{ $product->tags->pluck('name')->join(', ') }}
                @else
                    -
                @endif
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow mb-4 overflow-x-auto">
    <div class="px-4 py-3 font-semibold">Variants</div>
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Name</th>
                <th>SKU</th>
                <th>Grams</th>
                <th>Price</th>
                <th>HPP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($product->variants as $variant)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $variant->name }}</td>
                    <td>{{ $variant->sku ?? '-' }}</td>
                    <td>{{ number_format($variant->grams_per_unit ?? 0, 2, ',', '.') }}</td>
                    <td>Rp {{ number_format($variant->price_override ?? $product->base_price ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($variant->cost_price ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="border-t">
                    <td class="py-3 px-3 text-slate-500" colspan="5">No variants.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="bg-white rounded shadow mb-4 overflow-x-auto">
    <div class="px-4 py-3 font-semibold">Current Stock</div>
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Variant</th>
                <th>Qty (g)</th>
                <th>Min (g)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stocks as $stock)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $stock->variant?->name ?? 'All Variants' }}</td>
                    <td>{{ number_format($stock->qty_grams ?? 0, 2, ',', '.') }}</td>
                    <td>{{ number_format($stock->min_qty_grams ?? 0, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="border-t">
                    <td class="py-3 px-3 text-slate-500" colspan="3">No stock data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <div class="px-4 py-3 font-semibold">Stock Movement History</div>
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Date</th>
                <th>Type</th>
                <th>Variant</th>
                <th>Qty (g)</th>
                <th>Before</th>
                <th>After</th>
                <th>Reference</th>
                <th>Reason</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $movement->created_at?->format('d-m-Y H:i') ?? '-' }}</td>
                    <td class="uppercase text-xs">{{ $movement->type }}</td>
                    <td>{{ $movement->variant?->name ?? 'All Variants' }}</td>
                    <td>{{ number_format($movement->qty_grams ?? 0, 2, ',', '.') }}</td>
                    <td>{{ number_format($movement->before_qty_grams ?? 0, 2, ',', '.') }}</td>
                    <td>{{ number_format($movement->after_qty_grams ?? 0, 2, ',', '.') }}</td>
                    <td>
                        @if ($movement->reference_type)
                            {{ class_basename($movement->reference_type) }}#{{ $movement->reference_id }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $movement->reason ?? '-' }}</td>
                    <td>{{ $movement->creator?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr class="border-t">
                    <td class="py-3 px-3 text-slate-500" colspan="9">No stock movements.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $movements->links() }}</div>
@endsection
