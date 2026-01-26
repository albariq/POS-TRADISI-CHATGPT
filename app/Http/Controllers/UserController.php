<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['roles', 'outlets']);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $outlets = Outlet::orderBy('name')->get();

        return view('users.create', compact('roles', 'outlets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'locale' => ['nullable', 'string', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
            'outlet_ids' => ['required', 'array', 'min:1'],
            'outlet_ids.*' => ['integer', 'exists:outlets,id'],
            'default_outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
        ]);

        $defaultOutletId = $data['default_outlet_id'] ?? $data['outlet_ids'][0];
        if (! in_array($defaultOutletId, $data['outlet_ids'], true)) {
            $defaultOutletId = $data['outlet_ids'][0];
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'locale' => $data['locale'] ?? 'id',
            'is_active' => (bool) $data['is_active'],
        ]);

        $user->syncRoles([$data['role']]);

        $pivotData = [];
        foreach ($data['outlet_ids'] as $outletId) {
            $pivotData[$outletId] = ['is_default' => $outletId === $defaultOutletId];
        }
        $user->outlets()->sync($pivotData);

        AuditLogger::log('user_created', User::class, $user->id, null, $user->toArray());

        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $outlets = Outlet::orderBy('name')->get();
        $assignedOutletIds = $user->outlets->pluck('id')->all();
        $defaultOutletId = $user->outlets->firstWhere('pivot.is_default', true)?->id;

        return view('users.edit', compact('user', 'roles', 'outlets', 'assignedOutletIds', 'defaultOutletId'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'locale' => ['nullable', 'string', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
            'outlet_ids' => ['required', 'array', 'min:1'],
            'outlet_ids.*' => ['integer', 'exists:outlets,id'],
            'default_outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
        ]);

        $defaultOutletId = $data['default_outlet_id'] ?? $data['outlet_ids'][0];
        if (! in_array($defaultOutletId, $data['outlet_ids'], true)) {
            $defaultOutletId = $data['outlet_ids'][0];
        }

        $isActive = (bool) $data['is_active'];
        if ($user->id === $request->user()->id && ! $isActive) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        $isOwner = $user->hasRole('OWNER');
        $activeOwnerCount = User::role('OWNER')->where('is_active', true)->count();
        if ($isOwner && ($data['role'] !== 'OWNER' || ! $isActive) && $activeOwnerCount <= 1) {
            return back()->withErrors(['role' => 'At least one active OWNER must remain.']);
        }

        $before = $user->toArray();

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'locale' => $data['locale'] ?? $user->locale,
            'is_active' => $isActive,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->syncRoles([$data['role']]);

        $pivotData = [];
        foreach ($data['outlet_ids'] as $outletId) {
            $pivotData[$outletId] = ['is_default' => $outletId === $defaultOutletId];
        }
        $user->outlets()->sync($pivotData);

        AuditLogger::log('user_updated', User::class, $user->id, $before, $user->toArray());

        return redirect()->route('users.index');
    }
}
