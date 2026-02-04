<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Support\AuditLogger;
use App\Support\OutletContext;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::where('outlet_id', OutletContext::id())->orderBy('name')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'redirect_back' => ['nullable', 'boolean'],
        ]);

        $customer = Customer::create([
            'outlet_id' => OutletContext::id(),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        AuditLogger::log('customer_created', Customer::class, $customer->id, null, $customer->toArray());

        if (! empty($data['redirect_back'])) {
            return redirect()->back()->with('customer_created_id', $customer->id);
        }

        return redirect()->route('customers.index');
    }

    public function edit(Customer $customer)
    {
        if ($customer->outlet_id !== OutletContext::id()) {
            abort(403);
        }
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->outlet_id !== OutletContext::id()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $customer->toArray();
        $customer->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        AuditLogger::log('customer_updated', Customer::class, $customer->id, $before, $customer->toArray());

        return redirect()->route('customers.index');
    }
}
