@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Product</h1>

<form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="sku" class="border border-slate-300 rounded px-3 py-2" value="{{ $product->sku }}" placeholder="SKU" required>
        <input name="name" class="border border-slate-300 rounded px-3 py-2" value="{{ $product->name }}" placeholder="Name" required>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="base_price" class="border border-slate-300 rounded px-3 py-2" value="{{ $product->base_price }}" placeholder="Base price" required>
        <input name="cost_price" class="border border-slate-300 rounded px-3 py-2" value="{{ $product->cost_price }}" placeholder="Cost price (optional)">
    </div>
    <input name="barcode" class="border border-slate-300 rounded px-3 py-2 w-full" value="{{ $product->barcode }}" placeholder="Barcode">
    <textarea name="description" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Description">{{ $product->description }}</textarea>
    <select name="category_id" class="border border-slate-300 rounded px-3 py-2 w-full">
        <option value="" @selected(! $product->category_id)>Select category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>

    <div>
        <div class="text-sm text-slate-600 mb-2">Tags</div>
        <div class="flex flex-wrap gap-2">
            @foreach ($tags as $tag)
                <label class="text-sm"><input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked($product->tags->contains($tag))> {{ $tag->name }}</label>
            @endforeach
        </div>
    </div>

    <div>
        <div class="font-semibold mb-2">Variants</div>
        <div class="space-y-2">
            @foreach ($product->variants as $index => $variant)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                    <input name="variants[{{ $index }}][name]" class="border border-slate-300 rounded px-3 py-2" value="{{ $variant->name }}" placeholder="Variant name">
                    <input name="variants[{{ $index }}][sku]" class="border border-slate-300 rounded px-3 py-2" value="{{ $variant->sku }}" placeholder="Variant SKU">
                    <input name="variants[{{ $index }}][price_override]" class="border border-slate-300 rounded px-3 py-2" value="{{ $variant->price_override }}" placeholder="Price override">
                    <input name="variants[{{ $index }}][grams_per_unit]" class="border border-slate-300 rounded px-3 py-2" value="{{ $variant->grams_per_unit }}" placeholder="Grams per pcs">
                </div>
            @endforeach
            @for ($i = 0; $i < 2; $i++)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    <input type="hidden" name="variants[new{{ $i }}][id]" value="">
                    <input name="variants[new{{ $i }}][name]" class="border border-slate-300 rounded px-3 py-2" placeholder="Variant name">
                    <input name="variants[new{{ $i }}][sku]" class="border border-slate-300 rounded px-3 py-2" placeholder="Variant SKU">
                    <input name="variants[new{{ $i }}][price_override]" class="border border-slate-300 rounded px-3 py-2" placeholder="Price override">
                    <input name="variants[new{{ $i }}][grams_per_unit]" class="border border-slate-300 rounded px-3 py-2" placeholder="Grams per pcs">
                </div>
            @endfor
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked($product->is_active)>
        Active
    </label>

    <button class="bg-slate-900 text-white rounded px-4 py-2">{{ __('app.submit') }}</button>
</form>
@endsection
