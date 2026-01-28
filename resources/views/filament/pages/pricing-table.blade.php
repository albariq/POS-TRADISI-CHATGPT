@php
    $sizes = [
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

<x-filament::page>
    <x-filament::section heading="Tabel Harga Kopi">
        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <div class="fi-prose overflow-x-auto">
                    <table class="min-w-[900px] text-xs">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="2" class="text-left whitespace-nowrap">Nama Kopi</th>
                                <th colspan="3">100 Gr</th>
                                <th colspan="3">200 Gr</th>
                                <th colspan="3">500 Gr</th>
                                <th colspan="3">1 Kg</th>
                            </tr>
                            <tr>
                                @foreach ([1,2,3,4] as $i)
                                <th class="{{ $loop->first ? '' : 'border-l-2 border-white/20 pl-4' }}">Harga</th>
                                <th>Modal</th>
                                <th class="border-r-2 border-white/20 pr-4">Margin</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->rows as $row)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $row['name'] }}</td>
                                @foreach (array_keys($sizes) as $grams)
                                        <td class="{{ $loop->first ? '' : 'border-l-2 border-white/20 pl-4' }}">{{ $formatRp($row['sizes'][$grams]['price']) }}</td>
                                        <td>{{ $formatRp($row['sizes'][$grams]['cost']) }}</td>
                                        <td class="border-r-2 border-white/20 pr-4">{{ $formatRp($row['sizes'][$grams]['margin']) }}</td>
                                @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="fi-prose">
                    <table class="text-xs">
                        <thead class="text-center">
                            <tr>
                                <th colspan="6">Tabel Modal</th>
                            </tr>
                            <tr>
                                <th class="text-left">Nama Kopi</th>
                                <th>1kg</th>
                                <th>1 Gr</th>
                                <th>100 Gr</th>
                                <th>200 Gr</th>
                                <th>500 Gr</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->rows as $row)
                                <tr>
                                    <td class="text-left whitespace-nowrap">{{ $row['name'] }}</td>
                                    <td>{{ $formatRp($row['base']['kg']) }}</td>
                                    <td>{{ $formatRp($row['base']['gr']) }}</td>
                                    <td>{{ $formatRp($row['base']['g100']) }}</td>
                                    <td>{{ $formatRp($row['base']['g200']) }}</td>
                                    <td>{{ $formatRp($row['base']['g500']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="fi-prose">
                    <table class="text-xs">
                        <thead class="text-center">
                            <tr>
                                <th colspan="4">Tabel DLL</th>
                            </tr>
                            <tr>
                                <th>100 Gr</th>
                                <th>200 Gr</th>
                                <th>500 Gr</th>
                                <th>1 Kg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($this->rows))
                                <tr>
                                    <td colspan="4">Belum ada data.</td>
                                </tr>
                            @else
                                @php $dll = $this->rows[0]['dll'] ?? ['g100' => null, 'g200' => null, 'g500' => null, 'g1000' => null]; @endphp
                                <tr>
                                    <td>{{ $formatRp($dll['g100']) }}</td>
                                    <td>{{ $formatRp($dll['g200']) }}</td>
                                    <td>{{ $formatRp($dll['g500']) }}</td>
                                    <td>{{ $formatRp($dll['g1000']) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament::page>
