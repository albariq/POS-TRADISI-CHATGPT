@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-xl font-semibold mb-4">{{ __('app.select_outlet') }}</h1>
    <form method="POST" action="{{ route('outlets.select.submit') }}" class="space-y-4">
        @csrf
        <select name="outlet_id" class="w-full border border-slate-300 rounded px-3 py-2" required>
            <option value="" disabled selected>Select outlet</option>
            @foreach ($outlets as $outlet)
                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
            @endforeach
        </select>
        <button class="w-full bg-slate-900 text-white rounded py-2">{{ __('app.submit') }}</button>
    </form>
</div>
@endsection
