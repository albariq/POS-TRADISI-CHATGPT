@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8">
        <div class="mb-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Kasir</h1>
                <span class="text-xs text-slate-500">{{ $products->count() }} items</span>
            </div>
            <form method="GET">
                <input name="q" value="{{ request('q') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm" placeholder="Cari produk / SKU / barcode">
            </form>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($products as $product)
                <div class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm">
                    <div class="font-semibold text-sm">{{ $product->name }}</div>
                    <div class="text-xs text-slate-500">{{ $product->sku }}</div>
                    <div class="text-sm mt-1">Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>
                    <form method="POST" action="{{ route('pos.add') }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button class="w-full text-sm bg-slate-900 text-white rounded-lg py-2">Add</button>
                    </form>
                    @if ($product->variants->count())
                        <div class="mt-3 text-xs text-slate-500">Varian</div>
                        <div class="space-y-2 mt-2">
                            @foreach ($product->variants as $variant)
                                <form method="POST" action="{{ route('pos.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="product_variant_id" value="{{ $variant->id }}">
                                    <button class="w-full text-left text-xs border border-slate-300 rounded-lg px-2.5 py-2">
                                        <div class="font-medium">{{ $variant->name }}</div>
                                        <div class="text-slate-500">Rp {{ number_format($variant->price_override ?? $product->base_price, 0, ',', '.') }}</div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm lg:sticky lg:top-24">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">{{ __('app.cart') }}</h2>
                <span class="text-xs text-slate-500">{{ count($cart['items']) }} items</span>
            </div>
            <div class="space-y-3">
                @forelse ($cart['items'] as $item)
                    <div class="border-b border-slate-100 pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium text-sm">{{ $item['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $item['sku'] }}</div>
                            </div>
                            <form method="POST" action="{{ route('pos.remove') }}">
                                @csrf
                                <input type="hidden" name="key" value="{{ $item['key'] }}">
                                <button class="text-xs text-rose-600">Remove</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('pos.update') }}" class="mt-2 space-y-2">
                            @csrf
                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                            <input type="number" name="qty" class="w-full border border-slate-300 rounded-lg px-2.5 py-2 text-sm" value="{{ $item['qty'] }}" placeholder="Qty">
                            <input type="text" name="note" class="w-full border border-slate-300 rounded-lg px-2.5 py-2 text-sm" value="{{ $item['note'] }}" placeholder="Note">
                            <button class="text-xs text-slate-600">Update</button>
                        </form>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">Keranjang kosong</div>
                @endforelse
            </div>

            <div class="mt-4 rounded-lg bg-slate-50 p-3 space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>{{ __('app.tax') }}</span><span>Rp {{ number_format($totals['tax_total'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>{{ __('app.service') }}</span><span>Rp {{ number_format($totals['service_total'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between font-semibold text-base"><span>{{ __('app.grand_total') }}</span><span>Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}</span></div>
            </div>

            <form method="POST" action="{{ route('pos.hold') }}" class="mt-3">
                @csrf
                <button class="w-full text-xs border border-slate-300 rounded-lg py-2">Tahan / Parkir</button>
            </form>

            <form method="POST" action="{{ route('pos.checkout') }}" class="mt-3 space-y-2">
                @csrf
                <select id="payment-method" name="payment_method" class="w-full border border-slate-300 rounded-lg px-2.5 py-2 text-sm">
                        <option value="" disabled selected>Pilih metode pembayaran</option>
                        <option value="cash">Cash</option>
                        <option value="card">Debit/Kartu</option>
                        <option value="qris">QRIS</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="transfer">Transfer</option>
                    </select>
                <div id="cash-section" class="space-y-2 hidden">
                    <input id="cash-received" type="number" name="cash_received" class="w-full border border-slate-300 rounded-lg px-2.5 py-2 text-sm" placeholder="Uang diterima">
                    <div id="change-display" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                        Kembalian: Rp 0
                    </div>
                </div>
                <input type="text" name="payment_reference" class="w-full border border-slate-300 rounded-lg px-2.5 py-2 text-sm" placeholder="Referensi (opsional)">
                <select name="customer_id" class="w-full border border-slate-300 rounded-lg px-2.5 py-2 text-sm">
                    <option value="" selected>Pilih pelanggan (opsional)</option>
                    <option value="">Umum</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <button class="w-full bg-emerald-600 text-white rounded-lg py-2 text-sm">{{ __('app.checkout') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const grandTotal = Number(@json((float) $totals['grand_total'])) || 0;
        const methodSelect = document.getElementById('payment-method');
        const cashSection = document.getElementById('cash-section');
        const cashReceivedInput = document.getElementById('cash-received');
        const changeDisplay = document.getElementById('change-display');

        function formatIdr(value) {
            const rounded = Math.max(0, Math.round(value));
            return 'Rp ' + rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function updateCashVisibility() {
            const isCash = methodSelect?.value === 'cash';
            cashSection?.classList.toggle('hidden', !isCash);

            if (!isCash) {
                if (cashReceivedInput) cashReceivedInput.value = '';
                changeDisplay?.classList.add('hidden');
            }
        }

        function updateChange() {
            if (methodSelect?.value !== 'cash') return;
            const received = Number(cashReceivedInput?.value || 0);
            const change = Math.max(0, received - grandTotal);
            changeDisplay.textContent = 'Kembalian: ' + formatIdr(change);
            changeDisplay.classList.toggle('hidden', received <= 0);
        }

        methodSelect?.addEventListener('change', () => {
            updateCashVisibility();
            updateChange();
        });

        cashReceivedInput?.addEventListener('input', updateChange);

        updateCashVisibility();
        updateChange();
    })();
</script>
@endsection
