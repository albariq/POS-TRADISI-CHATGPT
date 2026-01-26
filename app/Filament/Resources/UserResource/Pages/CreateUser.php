<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected string $roleName = '';

    protected array $outletIds = [];

    protected ?int $defaultOutletId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleName = (string) ($data['role'] ?? '');
        $this->outletIds = $data['outlet_ids'] ?? [];
        $this->defaultOutletId = $data['default_outlet_id'] ?? null;

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        unset($data['role'], $data['outlet_ids'], $data['default_outlet_id']);

        return $data;
    }

    protected function afterCreate(): void
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
