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
                        <option value="{{ $customer->id }}" @selected((int) session('customer_created_id') === (int) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
                @if (auth()->user()?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER', 'CASHIER']))
                    <button type="button" id="open-customer-modal" class="inline-flex items-center text-xs text-slate-600 hover:text-slate-900">
                        + Tambah pelanggan
                    </button>
                @endif
                <button class="w-full bg-emerald-600 text-white rounded-lg py-2 text-sm">{{ __('app.checkout') }}</button>
            </form>
        </div>
    </div>
</div>

@if (auth()->user()?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER', 'CASHIER']))
    <div id="customer-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60" data-close-customer-modal></div>
        <div class="relative mx-auto mt-24 w-full max-w-md rounded-xl bg-white p-5 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold">Tambah Pelanggan</h3>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close-customer-modal>✕</button>
            </div>
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="redirect_back" value="1">
                <div>
                    <label class="text-xs text-slate-600">Nama</label>
                    <input name="name" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Nama pelanggan">
                </div>
                <div>
                    <label class="text-xs text-slate-600">Telepon</label>
                    <input name="phone" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="text-xs text-slate-600">Email</label>
                    <input type="email" name="email" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="email@contoh.com">
                </div>
                <div>
                    <label class="text-xs text-slate-600">Alamat</label>
                    <textarea name="address" rows="2" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Alamat (opsional)"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-xs" data-close-customer-modal>Batal</button>
                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endif

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
@if (auth()->user()?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER', 'CASHIER']))
<script>
    (function () {
        const openButton = document.getElementById('open-customer-modal');
        const modal = document.getElementById('customer-modal');
        if (!openButton || !modal) return;

        function openModal() {
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        openButton.addEventListener('click', openModal);
        modal.querySelectorAll('[data-close-customer-modal]').forEach((el) => {
            el.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    })();
</script>
@endif
@endsection
