<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EnsureActiveOutlet
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $outletId = Session::get('active_outlet_id');
        if ($outletId) {
            return $next($request);
        }

        $user = Auth::user();
        $outletCount = $user->outlets()->count();
        if ($outletCount === 1) {
            $singleOutlet = $user->outlets()->first();
            Session::put('active_outlet_id', $singleOutlet->id);
            return $next($request);
        }

        if (! $request->is('outlets/select') && ! $request->is('outlets/select/*')) {
            return redirect()->route('outlets.select');
        }

        return $next($request);
    }
}
