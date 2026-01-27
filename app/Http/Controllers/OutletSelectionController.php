<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OutletSelectionController extends Controller
{
    public function index()
    {
        $outlets = Auth::user()->outlets()->where('is_active', true)->get();
        return view('outlets.select', compact('outlets'));
    }

    public function select(Request $request)
    {
        $data = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
        ]);

        $outlet = Outlet::findOrFail($data['outlet_id']);
        if (! Auth::user()->outlets->contains($outlet)) {
            abort(403);
        }

        Session::put('active_outlet_id', $outlet->id);

        $user = Auth::user();
        $defaultRoute = $user->hasRole('CASHIER')
            ? route('pos.index')
            : url('/admin');

        return redirect()->to($defaultRoute);
    }
}
