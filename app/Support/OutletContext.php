<?php

namespace App\Support;

use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OutletContext
{
    public static function id(): ?int
    {
        $outletId = Session::get('active_outlet_id');
        if ($outletId) {
            return (int) $outletId;
        }

        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $defaultOutlet = $user->defaultOutlet();
        return $defaultOutlet?->id;
    }

    public static function outlet(): ?Outlet
    {
        $outletId = self::id();
        return $outletId ? Outlet::find($outletId) : null;
    }
}
