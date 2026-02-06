@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold">{{ __('app.customers') }}</h1>
    <a href="{{ route('customers.create') }}" class="bg-slate-900 text-white rounded px-3 py-2 text-sm">New Customer</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">Name</th>
                <th>Phone</th>
                <th>Points</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr class="border-t">
                    <td class="py-2 px-3">{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->points_balance }}</td>
                    <td>{{ $customer->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        @role('OWNER|ADMIN|MANAGER')
                            <a href="{{ route('customers.edit', $customer) }}" class="text-sm text-blue-600">Edit</a>
                        @endrole
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
