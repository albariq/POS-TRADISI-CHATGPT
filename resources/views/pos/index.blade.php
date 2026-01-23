@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <form method="GET" class="mb-3">
            <input name="q" value="{{ request('q') }}" class="w-full border border-slate-300 rounded px-3 py-2" placeholder="{{ __('app.search') }} product / SKU / barcode">
        </form>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($products as $product)
                <div class="bg-white rounded shadow p-3">
                    <div class="font-semibold">{{ $product->name }}</div>
                    <div class="text-xs text-slate-500">{{ $product->sku }}</div>
                    <div class="text-sm mt-1">Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>
                    <form method="POST" action="{{ route('pos.add') }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button class="w-full text-sm bg-slate-900 text-white rounded py-1">Add</button>
                    </form>
                    @if ($product->variants->count())
                        <div class="mt-2 text-xs text-slate-500">Variants:</div>
                        <div class="space-y-1 mt-1">
                            @foreach ($product->variants as $variant)
                                <form method="POST" action="{{ route('pos.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="product_variant_id" value="{{ $variant->id }}">
                                    <button class="w-full text-xs border border-slate-300 rounded py-1">
                                        {{ $variant->name }} - Rp {{ number_format($variant->price_override ?? $product->base_price, 0, ',', '.') }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">{{ __('app.cart') }}</h2>
        <div class="space-y-3">
            @forelse ($cart['items'] as $item)
                <div class="border-b pb-2">
                    <div class="font-medium text-sm">{{ $item['name'] }}</div>
                    <div class="text-xs text-slate-500">{{ $item['sku'] }}</div>
                    <form method="POST" action="{{ route('pos.update') }}" class="mt-2 space-y-2">
                        @csrf
                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                        <div class="flex gap-2">
                            <input type="number" name="qty" class="w-20 border border-slate-300 rounded px-2 py-1 text-sm" value="{{ $item['qty'] }}" placeholder="Qty">
                            <input type="number" name="discount_amount" class="flex-1 border border-slate-300 rounded px-2 py-1 text-sm" value="{{ $item['discount_amount'] }}" placeholder="Item discount">
                        </div>
                        <input type="text" name="note" class="w-full border border-slate-300 rounded px-2 py-1 text-sm" value="{{ $item['note'] }}" placeholder="Note">
                        <button class="text-xs text-slate-600">Update</button>
                    </form>
                    <form method="POST" action="{{ route('pos.remove') }}">
                        @csrf
                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                        <button class="text-xs text-rose-600 mt-1">Remove</button>
                    </form>
                </div>
            @empty
                <div class="text-sm text-slate-500">Cart empty</div>
            @endforelse
        </div>

        <div class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>{{ __('app.discount') }}</span><span>Rp {{ number_format($totals['discount_total'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>{{ __('app.tax') }}</span><span>Rp {{ number_format($totals['tax_total'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>{{ __('app.service') }}</span><span>Rp {{ number_format($totals['service_total'], 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-semibold"><span>{{ __('app.grand_total') }}</span><span>Rp {{ number_format($totals['grand_total'], 0, ',', '.') }}</span></div>
        </div>

        <form method="POST" action="{{ route('pos.discount') }}" class="mt-3 flex gap-2">
            @csrf
            <input type="number" step="0.01" name="transaction_discount" class="flex-1 border border-slate-300 rounded px-2 py-1 text-sm" value="{{ $cart['transaction_discount'] }}" placeholder="Transaction discount">
            <button class="text-xs bg-slate-200 rounded px-2">Apply</button>
        </form>

        <form method="POST" action="{{ route('pos.coupon') }}" class="mt-2 flex gap-2">
            @csrf
            <input type="text" name="code" class="flex-1 border border-slate-300 rounded px-2 py-1 text-sm" placeholder="Coupon code">
            <button class="text-xs bg-slate-200 rounded px-2">Apply</button>
        </form>

        <form method="POST" action="{{ route('pos.hold') }}" class="mt-3">
            @csrf
            <button class="w-full text-xs border border-slate-300 rounded py-2">Hold / Park</button>
        </form>

        <form method="POST" action="{{ route('pos.checkout') }}" class="mt-3 space-y-2">
            @csrf
            <select name="payment_method" class="w-full border border-slate-300 rounded px-2 py-2 text-sm">
                <option value="" disabled selected>Select payment method</option>
                <option value="cash">Cash</option>
                <option value="card">Debit/Kartu</option>
                <option value="qris">QRIS</option>
                <option value="ewallet">E-Wallet</option>
                <option value="transfer">Transfer</option>
            </select>
            <input type="number" name="payment_amount" class="w-full border border-slate-300 rounded px-2 py-2 text-sm" placeholder="Payment amount">
            <input type="number" name="cash_received" class="w-full border border-slate-300 rounded px-2 py-2 text-sm" placeholder="Cash received (optional)">
            <input type="text" name="payment_reference" class="w-full border border-slate-300 rounded px-2 py-2 text-sm" placeholder="Reference (optional)">
            <select name="customer_id" class="w-full border border-slate-300 rounded px-2 py-2 text-sm">
                <option value="" selected>Select customer (optional)</option>
                <option value="">Walk-in</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            <button class="w-full bg-emerald-600 text-white rounded py-2">{{ __('app.checkout') }}</button>
        </form>
    </div>
</div>
@endsection
