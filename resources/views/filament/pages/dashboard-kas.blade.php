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

            <div class="fi-prose">
                <h3>Saldo Shift Berjalan</h3>
                @if (empty($this->shiftSnapshot))
                    <p class="text-gray-400">Tidak ada shift aktif.</p>
                @else
                    <table class="text-sm">
                        <tbody>
                            <tr>
                                <td>Buka</td>
                                <td>{{ $this->shiftSnapshot['opened_at']?->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>Kas Awal</td>
                                <td>{{ $rupiah($this->shiftSnapshot['opening_balance'] ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Kas Masuk</td>
                                <td>{{ $rupiah($this->shiftSnapshot['cash_in'] ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Kas Keluar</td>
                                <td>{{ $rupiah($this->shiftSnapshot['cash_out'] ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td>Estimasi</td>
                                <td>{{ $rupiah($this->shiftSnapshot['expected'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
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

            <div class="fi-prose">
                <h3>Kas Masuk (Pembayaran Tunai)</h3>
                <table class="text-sm">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Waktu</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->recentCashSales as $row)
                            <tr>
                                <td>{{ $row['receipt'] ?? '-' }}</td>
                                <td>{{ $row['paid_at']?->format('d M H:i') }}</td>
                                <td>{{ $rupiah($row['amount']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Belum ada data.</td>
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
