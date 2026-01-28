@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Setting Harga</h1>
    <a href="{{ route('pricing-settings.create') }}" class="bg-slate-900 text-white rounded px-3 py-2 text-sm">Tambah</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500 bg-slate-50">
            <tr>
                <th class="py-2 px-3">Ukuran</th>
                <th class="py-2 px-3">Biaya Kemasan</th>
                <th class="py-2 px-3">Markup</th>
                <th class="py-2 px-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($settings as $setting)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $setting->grams === 1000 ? '1 Kg' : $setting->grams.' Gr' }}</td>
                    <td class="py-2 px-3">Rp {{ number_format($setting->packaging_cost, 0, ',', '.') }}</td>
                    <td class="py-2 px-3">{{ number_format($setting->markup * 100, 2) }}%</td>
                    <td class="py-2 px-3 text-right">
                        <a href="{{ route('pricing-settings.edit', $setting) }}" class="text-blue-600 text-sm">Edit</a>
                        <form method="POST" action="{{ route('pricing-settings.destroy', $setting) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 text-sm ml-2" onclick="return confirm('Hapus setting ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr class="border-t">
                    <td colspan="4" class="py-6 text-center text-slate-500">Belum ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
