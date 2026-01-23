<div class="bg-white border border-slate-200 p-4" style="width: 80mm;">
    <div class="text-center text-sm">
        <div class="font-semibold">{{ $sale->outlet->name }}</div>
        <div class="text-xs">{{ $sale->outlet->address }}</div>
        <div class="text-xs">{{ $sale->outlet->phone }}</div>
    </div>

    <div class="text-xs mt-3 space-y-1">
        <div>Receipt: {{ $sale->receipt_number }}</div>
        <div>Date: {{ $sale->created_at }}</div>
        <div>Cashier: {{ $sale->cashier?->name }}</div>
    </div>

    <div class="border-t border-dashed my-3"></div>

    <div class="text-xs space-y-1">
        @foreach ($sale->items as $item)
            <div class="flex justify-between">
                <span>{{ $item->name_snapshot }} x{{ $item->qty }}</span>
                <span>{{ number_format($item->line_total, 0, ',', '.') }}</span>
            </div>
            @if ($item->note)
                <div class="text-[10px] text-slate-500">{{ $item->note }}</div>
            @endif
        @endforeach
    </div>

    <div class="border-t border-dashed my-3"></div>

    <div class="text-xs space-y-1">
        <div class="flex justify-between"><span>Subtotal</span><span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
        <div class="flex justify-between"><span>Discount</span><span>{{ number_format($sale->discount_total, 0, ',', '.') }}</span></div>
        <div class="flex justify-between"><span>Tax</span><span>{{ number_format($sale->tax_total, 0, ',', '.') }}</span></div>
        <div class="flex justify-between"><span>Service</span><span>{{ number_format($sale->service_total, 0, ',', '.') }}</span></div>
        <div class="flex justify-between font-semibold"><span>Total</span><span>{{ number_format($sale->grand_total, 0, ',', '.') }}</span></div>
    </div>

    <div class="border-t border-dashed my-3"></div>

    <div class="text-xs">
        @foreach ($sale->payments as $payment)
            <div class="flex justify-between">
                <span>{{ strtoupper($payment->method) }}</span>
                <span>{{ number_format($payment->amount, 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>

    <div class="text-center text-xs mt-4">
        Thank you
    </div>
</div>
