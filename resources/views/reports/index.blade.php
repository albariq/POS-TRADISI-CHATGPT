@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">{{ __('app.reports') }}</h1>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <a href="{{ route('reports.sales') }}" class="bg-white rounded shadow p-4">Sales Report</a>
    <a href="{{ route('reports.inventory') }}" class="bg-white rounded shadow p-4">Inventory Report</a>
</div>
@endsection
