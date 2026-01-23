<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale');
        if (! $locale && Auth::check()) {
            $locale = Auth::user()->locale ?? 'id';
        }

        App::setLocale($locale ?? 'id');

        return $next($request);
    }
}
