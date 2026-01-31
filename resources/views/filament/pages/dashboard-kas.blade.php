@php
    $rupiah = function ($value) {
        return 'Rp' . number_format((float) $value, 0, ',', '.');
    };
    $maxNet = collect($this->series)->max('net') ?: 1;
@endphp

<x-filament::page>
    <x-filament::section heading="Dashboard Kas">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-xs uppercase tracking-wide text-gray-400">Hari Ini</div>
                <div class="mt-3 grid gap-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Masuk</span>
                        <span class="font-medium text-emerald-300">{{ $rupiah($this->summary['today_in'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Keluar</span>
                        <span class="font-medium text-rose-300">{{ $rupiah($this->summary['today_out'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="mt-3 rounded-md bg-white/5 px-3 py-2 text-sm font-semibold">
                    Net: {{ $rupiah($this->summary['today_net'] ?? 0) }}
                </div>
            </div>
            <div class="rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-xs uppercase tracking-wide text-gray-400">Minggu Ini</div>
                <div class="mt-3 grid gap-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Masuk</span>
                        <span class="font-medium text-emerald-300">{{ $rupiah($this->summary['week_in'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Keluar</span>
                        <span class="font-medium text-rose-300">{{ $rupiah($this->summary['week_out'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="mt-3 rounded-md bg-white/5 px-3 py-2 text-sm font-semibold">
                    Net: {{ $rupiah($this->summary['week_net'] ?? 0) }}
                </div>
            </div>
            <div class="rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-xs uppercase tracking-wide text-gray-400">Bulan Ini</div>
                <div class="mt-3 grid gap-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Masuk</span>
                        <span class="font-medium text-emerald-300">{{ $rupiah($this->summary['month_in'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Keluar</span>
                        <span class="font-medium text-rose-300">{{ $rupiah($this->summary['month_out'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="mt-3 rounded-md bg-white/5 px-3 py-2 text-sm font-semibold">
                    Net: {{ $rupiah($this->summary['month_net'] ?? 0) }}
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-sm font-semibold mb-3">Arus Kas 7 Hari Terakhir</div>
                <div class="space-y-3">
                    @foreach ($this->series as $point)
                        @php $width = max(6, (int) (($point['net'] / $maxNet) * 100)); @endphp
                        <div class="grid grid-cols-[56px_1fr_96px] items-center gap-3">
                            <div class="text-xs text-gray-400">{{ $point['label'] }}</div>
                            <div class="h-2 rounded-full bg-white/10">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $width }}%"></div>
                            </div>
                            <div class="text-xs text-right text-gray-200">{{ $rupiah($point['net']) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-sm font-semibold mb-3">Saldo Shift Berjalan</div>
                @if (empty($this->shiftSnapshot))
                    <div class="text-sm text-gray-400">Tidak ada shift aktif.</div>
                @else
                    <div class="text-xs text-gray-400">Buka: {{ $this->shiftSnapshot['opened_at']?->format('d M Y H:i') }}</div>
                    <div class="mt-3 grid gap-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-300">Kas Awal</span><span>{{ $rupiah($this->shiftSnapshot['opening_balance'] ?? 0) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-300">Kas Masuk</span><span>{{ $rupiah($this->shiftSnapshot['cash_in'] ?? 0) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-300">Kas Keluar</span><span>{{ $rupiah($this->shiftSnapshot['cash_out'] ?? 0) }}</span></div>
                    </div>
                    <div class="mt-3 rounded-md bg-white/5 px-3 py-2 text-sm font-semibold">
                        Estimasi: {{ $rupiah($this->shiftSnapshot['expected'] ?? 0) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-sm font-semibold mb-3">Kas Masuk (Pembayaran Tunai)</div>
                <div class="space-y-2 text-sm">
                    @forelse ($this->recentCashSales as $row)
                        <div class="flex justify-between">
                            <div class="text-gray-300">{{ $row['receipt'] ?? '-' }}</div>
                            <div class="text-gray-100">{{ $rupiah($row['amount']) }}</div>
                        </div>
                    @empty
                        <div class="text-gray-400">Belum ada data.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-white/10 bg-gray-900/30 p-4">
                <div class="text-sm font-semibold mb-3">Mutasi Kas (In/Out)</div>
                <div class="space-y-2 text-sm">
                    @forelse ($this->recentMovements as $row)
                        <div class="flex justify-between">
                            <div class="text-gray-300">{{ strtoupper($row['type']) }} • {{ $row['reason'] ?? '-' }}</div>
                            <div class="text-gray-100">{{ $rupiah($row['amount']) }}</div>
                        </div>
                    @empty
                        <div class="text-gray-400">Belum ada data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament::page>
