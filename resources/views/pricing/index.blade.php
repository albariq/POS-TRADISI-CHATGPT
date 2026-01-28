@extends('layouts.app')

@section('content')
@php
    $sizeLabels = [
        100 => '100 Gr',
        200 => '200 Gr',
        500 => '500 Gr',
        1000 => '1 Kg',
    ];
    $priceLabels = [
        100 => '100 Gr (50+5)',
        200 => '200 Gr (45+5)',
        500 => '500 Gr (40+3)',
        1000 => '1 Kg (35+2)',
    ];
    $formatRp = function ($value) {
        if ($value === null) {
            return '-';
        }
        return 'Rp' . number_format($value, 0, ',', '.');
    };
@endphp

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Tabel Harga Kopi</h1>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
    <div class="xl:col-span-9 bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead class="text-left text-slate-500 bg-slate-50">
                <tr>
                    <th class="py-2 px-3">Nama Kopi</th>
                    @foreach ($sizeLabels as $grams => $label)
                        <th class="py-2 px-3">Harga {{ $priceLabels[$grams] }}</th>
                        <th class="py-2 px-3">Modal {{ $label }}</th>
                        <th class="py-2 px-3">Margin {{ $label }}</th>
                    @endforeach
                    <th class="py-2 px-3">Harga 1kg</th>
                    <th class="py-2 px-3">Harga 1 Gr</th>
                    <th class="py-2 px-3">Harga 100 Gr</th>
                    <th class="py-2 px-3">Harga 200 Gr</th>
                    <th class="py-2 px-3">Harga 500 Gr</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-t">
                        <td class="py-2 px-3 font-medium text-slate-900 whitespace-nowrap">{{ $row['name'] }}</td>
                        @foreach (array_keys($sizeLabels) as $grams)
                            <td class="py-2 px-3">{{ $formatRp($row['sizes'][$grams]['price']) }}</td>
                            <td class="py-2 px-3">{{ $formatRp($row['sizes'][$grams]['cost']) }}</td>
                            <td class="py-2 px-3">{{ $formatRp($row['sizes'][$grams]['margin']) }}</td>
                        @endforeach
                        <td class="py-2 px-3">{{ $formatRp($row['base']['kg']) }}</td>
                        <td class="py-2 px-3">{{ $formatRp($row['base']['gr']) }}</td>
                        <td class="py-2 px-3">{{ $formatRp($row['base']['g100']) }}</td>
                        <td class="py-2 px-3">{{ $formatRp($row['base']['g200']) }}</td>
                        <td class="py-2 px-3">{{ $formatRp($row['base']['g500']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="xl:col-span-3">
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="px-3 py-2 border-b text-sm font-semibold text-slate-700">Type</div>
            <table class="min-w-full text-xs">
                <thead class="text-left text-slate-500 bg-slate-50">
                    <tr>
                        <th class="py-2 px-3">Type</th>
                        <th class="py-2 px-3">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($packagingCosts as $grams => $cost)
                        <tr class="border-t">
                            <td class="py-2 px-3">{{ $grams === 1000 ? '1 Kg' : $grams.' Gr' }}</td>
                            <td class="py-2 px-3">{{ $formatRp($cost) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
