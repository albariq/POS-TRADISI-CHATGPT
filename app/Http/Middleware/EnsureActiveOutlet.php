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
        $activeOutlets = $user->outlets()->where('is_active', true);
        $activeOutletCount = $activeOutlets->count();
        $isCashier = $user->hasRole('CASHIER');

        // Only cashiers with multiple active outlets should be forced to choose.
        if ($isCashier && $activeOutletCount > 1) {
            if (! $request->is('outlets/select') && ! $request->is('outlets/select/*')) {
                return redirect()->route('outlets.select');
            }

            return $next($request);
        }

        // For everyone else (or cashiers with 0/1 active outlet), auto-select.
        $defaultActiveOutlet = $user->outlets()
            ->wherePivot('is_default', true)
            ->where('is_active', true)
            ->first();

        $outletToUse = $defaultActiveOutlet ?? $activeOutlets->first();

        if ($outletToUse) {
            Session::put('active_outlet_id', $outletToUse->id);
        }

        return $next($request);
    }
}
