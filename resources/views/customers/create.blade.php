@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">New Customer</h1>
<form method="POST" action="{{ route('customers.store') }}" class="space-y-3">
    @csrf
    <input name="name" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Name" required>
    <input name="email" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Email">
    <input name="phone" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Phone">
    <textarea name="address" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Address"></textarea>
    <button class="bg-slate-900 text-white rounded px-4 py-2">{{ __('app.submit') }}</button>
</form>
@endsection
