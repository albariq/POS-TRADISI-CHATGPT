@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
    <div>
        <h1 class="text-xl font-semibold">{{ __('app.users') }}</h1>
        <p class="text-sm text-slate-500">{{ __('app.manage_users') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="GET" action="{{ route('users.index') }}" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" class="w-56 border border-slate-300 rounded px-3 py-2 text-sm" placeholder="{{ __('app.search') }}">
            <button class="bg-white border border-slate-300 text-slate-700 rounded px-3 py-2 text-sm">{{ __('app.filter') }}</button>
        </form>
        <a href="{{ route('users.create') }}" class="bg-slate-900 text-white rounded px-3 py-2 text-sm">{{ __('app.new_user') }}</a>
    </div>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="py-2 px-3">{{ __('app.name') }}</th>
                <th>{{ __('app.email') }}</th>
                <th>{{ __('app.role') }}</th>
                <th>{{ __('app.outlets') }}</th>
                <th>{{ __('app.status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr class="border-t">
                    <td class="py-2 px-3">
                        <div class="font-medium">{{ $user->name }}</div>
                        <div class="text-xs text-slate-500">{{ $user->locale }}</div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                    <td class="max-w-xs">
                        <div class="text-slate-700">{{ $user->outlets->pluck('name')->implode(', ') ?: '-' }}</div>
                        @php
                            $defaultOutlet = $user->outlets->firstWhere('pivot.is_default', true);
                        @endphp
                        <div class="text-xs text-slate-500">{{ __('app.default_outlet') }}: {{ $defaultOutlet?->name ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $user->is_active ? __('app.active') : __('app.inactive') }}
                        </span>
                    </td>
                    <td class="text-right pr-3">
                        <a href="{{ route('users.edit', $user) }}" class="text-sm text-blue-600">{{ __('app.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t">
                    <td colspan="6" class="py-4 px-3 text-center text-slate-500">{{ __('app.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
