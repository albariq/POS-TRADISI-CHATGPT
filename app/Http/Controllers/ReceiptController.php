<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Mail\ReceiptMail;
use App\Support\OutletContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReceiptController extends Controller
{
    public function show(Sale $sale)
    {
        if ($sale->outlet_id !== OutletContext::id()) {
            abort(403);
        }
        $sale->load('items', 'payments', 'customer', 'outlet');
        $message = urlencode("Receipt ".$sale->receipt_number."\nTotal: ".$sale->grand_total."\nLink: ".route('receipts.public', $sale->public_token));
        $waLink = "https://wa.me/?text=".$message;

        return view('receipts.show', compact('sale', 'waLink'));
    }

    public function public(string $token)
    {
        $sale = Sale::where('public_token', $token)->with('items', 'payments', 'customer', 'outlet')->firstOrFail();
        return view('receipts.public', compact('sale'));
    }

    public function email(Request $request, Sale $sale)
    {
        if ($sale->outlet_id !== OutletContext::id()) {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $sale->load('items', 'payments', 'customer', 'outlet');
        Mail::to($data['email'])->send(new ReceiptMail($sale));

        return back();
    }
}
