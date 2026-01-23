<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.app_name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="min-h-screen">
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 no-print">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="font-semibold">{{ __('app.app_name') }}</div>
                @auth
                    <div class="flex gap-3 items-center text-sm">
                        <a href="{{ route('dashboard') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.dashboard') }}</a>
                        <a href="{{ route('pos.index') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.pos') }}</a>
                        <a href="{{ route('products.index') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.products') }}</a>
                        <a href="{{ route('inventory.index') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.inventory') }}</a>
                        <a href="{{ route('customers.index') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.customers') }}</a>
                        <a href="{{ route('reports.index') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.reports') }}</a>
                        <a href="{{ route('shifts.index') }}" class="hover:text-slate-900 text-slate-600">{{ __('app.shifts') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-rose-600 hover:text-rose-700">{{ __('app.logout') }}</button>
                        </form>
                    </div>
                @endauth
            </div>
        </nav>

        <main class="max-w-6xl mx-auto px-4 py-6">
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded mb-4">
                    <ul class="text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
