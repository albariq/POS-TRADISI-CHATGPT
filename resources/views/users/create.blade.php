@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">{{ __('app.new_user') }}</h1>
<form method="POST" action="{{ route('users.store') }}" class="space-y-4">
    @csrf
    <div class="grid gap-3 md:grid-cols-2">
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.name') }}</label>
            <input name="name" value="{{ old('name') }}" class="border border-slate-300 rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.email') }}</label>
            <input name="email" type="email" value="{{ old('email') }}" class="border border-slate-300 rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.password') }}</label>
            <input name="password" type="password" class="border border-slate-300 rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.password_confirmation') }}</label>
            <input name="password_confirmation" type="password" class="border border-slate-300 rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.role') }}</label>
            <select name="role" class="border border-slate-300 rounded px-3 py-2 w-full" required>
                <option value="">{{ __('app.select_role') }}</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.locale') }}</label>
            <select name="locale" class="border border-slate-300 rounded px-3 py-2 w-full">
                <option value="id" @selected(old('locale', 'id') === 'id')>id</option>
                <option value="en" @selected(old('locale') === 'en')>en</option>
            </select>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded p-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">{{ __('app.outlets') }}</h2>
            <label class="text-sm text-slate-600 flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', 1))>
                {{ __('app.active') }}
            </label>
        </div>
        <div class="grid gap-3">
            @foreach ($outlets as $outlet)
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 border border-slate-100 rounded p-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="outlet_ids[]" value="{{ $outlet->id }}" class="rounded" @checked(in_array($outlet->id, old('outlet_ids', []), true))>
                        <span class="font-medium">{{ $outlet->name }}</span>
                        <span class="text-xs text-slate-500">{{ $outlet->code }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="radio" name="default_outlet_id" value="{{ $outlet->id }}" class="rounded" @checked(old('default_outlet_id') == $outlet->id)>
                        {{ __('app.default_outlet') }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <button class="bg-slate-900 text-white rounded px-4 py-2">{{ __('app.submit') }}</button>
</form>
@endsection
