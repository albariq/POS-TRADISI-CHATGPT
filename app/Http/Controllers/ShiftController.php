<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Shift;
use App\Support\AuditLogger;
use App\Support\OutletContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index()
    {
        $outletId = OutletContext::id();
        $currentShift = Shift::where('outlet_id', $outletId)->where('status', 'open')->latest()->first();
        $shifts = Shift::where('outlet_id', $outletId)->latest()->paginate(10);

        return view('shifts.index', compact('currentShift', 'shifts'));
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'opening_balance' => ['required', 'numeric'],
        ]);

        $shift = Shift::create([
            'outlet_id' => OutletContext::id(),
            'opened_by' => Auth::id(),
            'opened_at' => now(),
            'opening_balance' => $data['opening_balance'],
            'status' => 'open',
        ]);

        AuditLogger::log('shift_opened', Shift::class, $shift->id, null, $shift->toArray());

        return redirect()->route('shifts.index');
    }

    public function cashMovement(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric'],
            'reason' => ['nullable', 'string'],
        ]);

        $shift = Shift::where('outlet_id', OutletContext::id())->where('status', 'open')->latest()->first();
        if (! $shift) {
            return back()->withErrors(['shift' => 'No open shift.']);
        }

        $movement = CashMovement::create([
            'outlet_id' => $shift->outlet_id,
            'shift_id' => $shift->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'reason' => $data['reason'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $field = $data['type'] === 'in' ? 'cash_in' : 'cash_out';
        $shift->increment($field, $data['amount']);

        AuditLogger::log('cash_movement', CashMovement::class, $movement->id, null, $movement->toArray());

        return redirect()->route('shifts.index');
    }

    public function close(Request $request)
    {
        $data = $request->validate([
            'closing_balance_actual' => ['required', 'numeric'],
        ]);

        $shift = Shift::where('outlet_id', OutletContext::id())->where('status', 'open')->latest()->first();
        if (! $shift) {
            return back()->withErrors(['shift' => 'No open shift.']);
        }

        $expectedCash = Payment::where('outlet_id', $shift->outlet_id)
            ->where('method', 'cash')
            ->whereBetween('created_at', [$shift->opened_at, now()])
            ->sum('amount');

        $shift->update([
            'closed_by' => Auth::id(),
            'closed_at' => now(),
            'closing_balance_actual' => $data['closing_balance_actual'],
            'closing_balance_expected' => $shift->opening_balance + $expectedCash + $shift->cash_in - $shift->cash_out,
            'status' => 'closed',
        ]);

        AuditLogger::log('shift_closed', Shift::class, $shift->id, null, $shift->toArray());

        return redirect()->route('shifts.index');
    }
}
