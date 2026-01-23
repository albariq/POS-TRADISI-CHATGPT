@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-xl font-semibold mb-4">{{ __('app.login') }}</h1>
    <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.email') }}</label>
            <input type="email" name="email" class="w-full border border-slate-300 rounded px-3 py-2" placeholder="Email" required>
        </div>
        <div>
            <label class="block text-sm text-slate-600 mb-1">{{ __('app.password') }}</label>
            <input type="password" name="password" class="w-full border border-slate-300 rounded px-3 py-2" placeholder="Password" required>
        </div>
        <button class="w-full bg-slate-900 text-white rounded py-2">{{ __('app.login') }}</button>
    </form>
</div>
@endsection
