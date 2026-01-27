<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if (! Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Account is inactive.']);
            }

            $user = Auth::user();
            if ($user->hasRole('CASHIER')) {
                // Always send cashiers to POS, even if an intended URL exists (e.g. /admin).
                $request->session()->forget('url.intended');

                $activeOutletCount = $user->outlets()->where('is_active', true)->count();
                if ($activeOutletCount > 1) {
                    // Force outlet selection for cashiers with multiple active outlets.
                    $request->session()->forget('active_outlet_id');
                }

                return redirect()->route('pos.index');
            }

            return redirect()->intended(url('/admin'));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
