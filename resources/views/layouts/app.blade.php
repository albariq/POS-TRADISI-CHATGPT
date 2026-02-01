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
            <div class="max-w-6xl mx-auto px-4 py-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="font-semibold">{{ __('app.app_name') }}</div>
                </div>
                @auth
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        @role('CASHIER')
                            <a href="{{ route('pos.index') }}" class="px-2.5 py-1.5 rounded hover:text-slate-900 text-slate-600 hover:bg-slate-100 {{ request()->routeIs('pos.*') ? 'bg-slate-900 text-white hover:bg-slate-900 hover:text-white' : '' }}">{{ __('app.pos') }}</a>
                        @else
                            <a href="{{ url('/admin') }}" class="px-2.5 py-1.5 rounded hover:text-slate-900 text-slate-600 hover:bg-slate-100">Admin</a>
                            <a href="{{ route('pos.index') }}" class="px-2.5 py-1.5 rounded hover:text-slate-900 text-slate-600 hover:bg-slate-100 {{ request()->routeIs('pos.*') ? 'bg-slate-900 text-white hover:bg-slate-900 hover:text-white' : '' }}">{{ __('app.pos') }}</a>
                        @endrole
                        <div class="h-5 w-px bg-slate-200 mx-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="px-2.5 py-1.5 rounded text-rose-600 hover:text-rose-700 hover:bg-rose-50">{{ __('app.logout') }}</button>
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
