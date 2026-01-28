@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Setting Harga</h1>

<form method="POST" action="{{ route('pricing-settings.update', $pricingSetting) }}" class="bg-white rounded shadow p-4 max-w-xl">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="block text-sm mb-1">Ukuran</label>
        <select name="grams" class="w-full border border-slate-300 rounded px-3 py-2">
            @foreach ($sizes as $grams)
                <option value="{{ $grams }}" {{ (int) old('grams', $pricingSetting->grams) === $grams ? 'selected' : '' }}>
                    {{ $grams === 1000 ? '1 Kg' : $grams.' Gr' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="block text-sm mb-1">Biaya Kemasan</label>
        <input name="packaging_cost" type="number" min="0" value="{{ old('packaging_cost', $pricingSetting->packaging_cost) }}" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div class="mb-4">
        <label class="block text-sm mb-1">Markup (contoh: 0.55)</label>
        <input name="markup" type="number" step="0.001" min="0" value="{{ old('markup', $pricingSetting->markup) }}" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div class="flex gap-2">
        <button class="bg-slate-900 text-white rounded px-4 py-2">Simpan</button>
        <a href="{{ route('pricing-settings.index') }}" class="px-4 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
@endsection
