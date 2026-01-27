<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected array $outletIds = [];
    protected array $tagIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->outletIds = $this->validateOutletIds($data['outlet_ids'] ?? []);
        $this->tagIds = collect($data['tags'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        unset($data['outlet_ids']);
        unset($data['tags']);

        $data['has_variants'] = ! empty($data['variants']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->outlets()->sync($this->outletIds);
        $this->record->tags()->sync($this->tagIds);
    }

    protected function validateOutletIds(array $outletIds): array
    {
        $user = Auth::user();
        if (! $user) {
            throw ValidationException::withMessages([
                'outlet_ids' => 'User tidak terautentikasi.',
            ]);
        }

        $allowedOutletIds = $user->outlets()
            ->where('is_active', true)
            ->pluck('outlets.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedOutletIds = collect($outletIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $validOutletIds = array_values(array_intersect($selectedOutletIds, $allowedOutletIds));

        if (empty($validOutletIds)) {
            throw ValidationException::withMessages([
                'outlet_ids' => 'Pilih minimal satu outlet yang valid.',
            ]);
        }

        return $validOutletIds;
    }
}
