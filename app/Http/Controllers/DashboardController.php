<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $defaultRoute = $user->hasRole('CASHIER')
            ? route('pos.index')
            : url('/admin');

        return redirect()->to($defaultRoute);
    }
}
