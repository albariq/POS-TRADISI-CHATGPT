<p>Receipt {{ $sale->receipt_number }}</p>
<p>Total: {{ number_format($sale->grand_total, 0, ',', '.') }}</p>
<p>Open: <a href="{{ route('receipts.public', $sale->public_token) }}">{{ route('receipts.public', $sale->public_token) }}</a></p>
