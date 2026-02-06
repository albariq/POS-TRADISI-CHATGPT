@php
    $rupiah = function ($value) {
        return 'Rp' . number_format((float) $value, 0, ',', '.');
    };
@endphp

<x-filament::page>
    <x-filament::section heading="Dashboard Kas">
        <div class="grid gap-6 xl:grid-cols-2">
            <div class="fi-prose">
                <h3>Ringkasan</h3>
                <table class="text-sm">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Hari Ini</td>
                            <td>{{ $rupiah($this->summary['today_in'] ?? 0) }}</td>
                            <td>{{ $rupiah($this->summary['today_out'] ?? 0) }}</td>
                            <td>{{ $rupiah($this->summary['today_net'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Minggu Ini</td>
                            <td>{{ $rupiah($this->summary['week_in'] ?? 0) }}</td>
                            <td>{{ $rupiah($this->summary['week_out'] ?? 0) }}</td>
                            <td>{{ $rupiah($this->summary['week_net'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Bulan Ini</td>
                            <td>{{ $rupiah($this->summary['month_in'] ?? 0) }}</td>
                            <td>{{ $rupiah($this->summary['month_out'] ?? 0) }}</td>
                            <td>{{ $rupiah($this->summary['month_net'] ?? 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <div class="fi-prose">
                <h3>Arus Kas 7 Hari Terakhir</h3>
                <table class="text-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->series as $point)
                            <tr>
                                <td>{{ $point['label'] }}</td>
                                <td>{{ $rupiah($point['net']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 fi-prose">
            <h3>Mutasi Kas (In/Out)</h3>
            <table class="text-sm">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                        <th>User</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->recentMovements as $row)
                        <tr>
                            <td>{{ $row['created_at']?->format('d M H:i') }}</td>
                            <td>{{ strtoupper($row['type']) }}</td>
                            <td>{{ $row['reason'] ?? '-' }}</td>
                            <td>{{ $row['creator'] ?? '-' }}</td>
                            <td>{{ $rupiah($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament::page>
