@extends('layouts.app')

@section('content')
<div class="no-print mb-4 flex items-center gap-2">
    <a href="{{ route('receipts.public', $sale->public_token) }}" class="text-sm text-blue-600">Public link</a>
    <a href="{{ $waLink }}" class="text-sm text-green-600">Send WhatsApp</a>
    <form method="POST" action="{{ route('receipts.email', $sale) }}" class="flex items-center gap-2">
        @csrf
        <input type="email" name="email" class="border border-slate-300 rounded px-2 py-1 text-sm" placeholder="Email receipt">
        <button class="text-sm bg-slate-900 text-white rounded px-3 py-1">Send</button>
    </form>
    <button onclick="window.print()" class="text-sm bg-slate-900 text-white rounded px-3 py-1">Print</button>
</div>

@include('receipts.partials.receipt', ['sale' => $sale])
@endsection
