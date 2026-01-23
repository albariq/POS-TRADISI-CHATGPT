@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">{{ __('app.shifts') }}</h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-2">{{ __('app.open_shift') }}</h2>
        @if ($currentShift)
            <div class="text-sm text-slate-600">Shift open since {{ $currentShift->opened_at }}</div>
        @else
            <form method="POST" action="{{ route('shifts.open') }}" class="space-y-2">
                @csrf
                <input name="opening_balance" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Opening balance">
                <button class="bg-slate-900 text-white rounded px-3 py-2 text-sm">Open Shift</button>
            </form>
        @endif
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-2">Cash In/Out</h2>
        <form method="POST" action="{{ route('shifts.cash') }}" class="space-y-2">
            @csrf
            <select name="type" class="border border-slate-300 rounded px-3 py-2 w-full">
                <option value="" disabled selected>Select type</option>
                <option value="in">Cash In</option>
                <option value="out">Cash Out</option>
            </select>
            <input name="amount" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Amount">
            <input name="reason" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Reason">
            <button class="bg-slate-900 text-white rounded px-3 py-2 text-sm">Submit</button>
        </form>
    </div>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
    <h2 class="font-semibold mb-2">{{ __('app.close_shift') }}</h2>
    <form method="POST" action="{{ route('shifts.close') }}" class="space-y-2">
        @csrf
        <input name="closing_balance_actual" class="border border-slate-300 rounded px-3 py-2 w-full" placeholder="Closing balance actual">
        <button class="bg-rose-600 text-white rounded px-3 py-2 text-sm">Close Shift</button>
    </form>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Opened</th>
                <th>Status</th>
                <th>Opening</th>
                <th>Expected</th>
                <th>Actual</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shifts as $shift)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $shift->opened_at }}</td>
                    <td>{{ $shift->status }}</td>
                    <td>{{ number_format($shift->opening_balance, 0, ',', '.') }}</td>
                    <td>{{ number_format($shift->closing_balance_expected, 0, ',', '.') }}</td>
                    <td>{{ number_format($shift->closing_balance_actual ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $shifts->links() }}</div>
@endsection
