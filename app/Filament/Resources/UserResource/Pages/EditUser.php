<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected string $roleName = '';

    protected array $outletIds = [];

    protected ?int $defaultOutletId = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->roles->first()?->name;
        $data['outlet_ids'] = $this->record->outlets->pluck('id')->all();
        $data['default_outlet_id'] = $this->record->outlets->firstWhere('pivot.is_default', true)?->id;

        return $data;
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();
        $isActive = (bool) ($data['is_active'] ?? false);

        if (! $isActive && $this->record->id === auth()->id()) {
            throw ValidationException::withMessages([
                'is_active' => 'You cannot deactivate your own account.',
            ]);
        }

        $isOwner = $this->record->hasRole('OWNER');
        $activeOwnerCount = User::role('OWNER')->where('is_active', true)->count();
        $newRole = (string) ($data['role'] ?? '');

        if ($isOwner && ($newRole !== 'OWNER' || ! $isActive) && $activeOwnerCount <= 1) {
            throw ValidationException::withMessages([
                'role' => 'At least one active OWNER must remain.',
            ]);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleName = (string) ($data['role'] ?? '');
        $this->outletIds = $data['outlet_ids'] ?? [];
        $this->defaultOutletId = $data['default_outlet_id'] ?? null;

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        unset($data['role'], $data['outlet_ids'], $data['default_outlet_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->roleName !== '') {
            $this->record->syncRoles([$this->roleName]);
        }

        if (empty($this->outletIds)) {
            return;
        }

        $defaultOutletId = $this->defaultOutletId;
        if (! $defaultOutletId || ! in_array($defaultOutletId, $this->outletIds, true)) {
            $defaultOutletId = $this->outletIds[0];
        }

        $pivotData = [];
        foreach ($this->outletIds as $outletId) {
            $pivotData[$outletId] = ['is_default' => $outletId === $defaultOutletId];
        }

        $this->record->outlets()->sync($pivotData);
    }
}
