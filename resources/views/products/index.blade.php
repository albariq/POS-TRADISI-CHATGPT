@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold">{{ __('app.products') }}</h1>
    <a href="{{ route('products.create') }}" class="bg-slate-900 text-white rounded px-3 py-2 text-sm">New Product</a>
</div>

<form method="GET" class="mb-3">
    <input name="q" value="{{ request('q') }}" class="w-full border border-slate-300 rounded px-3 py-2" placeholder="{{ __('app.search') }}">
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">SKU</th>
                <th>Name</th>
                <th>Price</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                    <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                    <td><a href="{{ route('products.edit', $product) }}" class="text-sm text-blue-600">Edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
