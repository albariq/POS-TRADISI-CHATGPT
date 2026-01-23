@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Customer</h1>
<form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-3">
    @csrf
    @method('PUT')
    <input name="name" class="border border-slate-300 rounded px-3 py-2 w-full" value="{{ $customer->name }}" placeholder="Name" required>
    <input name="email" class="border border-slate-300 rounded px-3 py-2 w-full" value="{{ $customer->email }}" placeholder="Email">
    <input name="phone" class="border border-slate-300 rounded px-3 py-2 w-full" value="{{ $customer->phone }}" placeholder="Phone">
    <textarea name="address" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Address">{{ $customer->address }}</textarea>
    <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked($customer->is_active)>
        Active
    </label>
    <button class="bg-slate-900 text-white rounded px-4 py-2">{{ __('app.submit') }}</button>
</form>
@endsection
