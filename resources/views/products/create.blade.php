@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">New Product</h1>

<form method="POST" action="{{ route('products.store') }}" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="sku" class="border border-slate-300 rounded px-3 py-2" placeholder="SKU" required>
        <input name="name" class="border border-slate-300 rounded px-3 py-2" placeholder="Name" required>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="base_price" class="border border-slate-300 rounded px-3 py-2" placeholder="Base Price" required>
        <input name="cost_price" class="border border-slate-300 rounded px-3 py-2" placeholder="Cost Price (optional)">
    </div>
    <input name="barcode" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Barcode (optional)">
    <textarea name="description" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Description"></textarea>
    <select name="category_id" class="border border-slate-300 rounded px-3 py-2 w-full">
        <option value="" selected>Select category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>

    <div>
        <div class="text-sm text-slate-600 mb-2">Tags</div>
        <div class="flex flex-wrap gap-2">
            @foreach ($tags as $tag)
                <label class="text-sm"><input type="checkbox" name="tags[]" value="{{ $tag->id }}"> {{ $tag->name }}</label>
            @endforeach
        </div>
    </div>

    <div>
        <div class="font-semibold mb-2">Variants (optional)</div>
        <div class="space-y-2">
            @for ($i = 0; $i < 3; $i++)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    <input name="variants[{{ $i }}][name]" class="border border-slate-300 rounded px-3 py-2" placeholder="Variant name">
                    <input name="variants[{{ $i }}][sku]" class="border border-slate-300 rounded px-3 py-2" placeholder="Variant SKU">
                    <input name="variants[{{ $i }}][price_override]" class="border border-slate-300 rounded px-3 py-2" placeholder="Price override">
                    <input name="variants[{{ $i }}][cost_price]" class="border border-slate-300 rounded px-3 py-2" placeholder="Cost price">
                    <input name="variants[{{ $i }}][grams_per_unit]" class="border border-slate-300 rounded px-3 py-2" placeholder="Grams per pcs">
                </div>
            @endfor
        </div>
    </div>

    <button class="bg-slate-900 text-white rounded px-4 py-2">{{ __('app.submit') }}</button>
</form>
@endsection
