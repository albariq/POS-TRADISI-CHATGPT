<?php

namespace App\Filament\Resources\LabaRugiEntryResource\Pages;

use App\Filament\Resources\LabaRugiEntryResource;
use App\Models\User;
use App\Support\OutletContext;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditLabaRugiEntry extends EditRecord
{
    protected static string $resource = LabaRugiEntryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $outletId = $data['outlet_id'] ?? $this->record?->outlet_id ?? OutletContext::id();
        if (! $outletId) {
            /** @var User|null $user */
            $user = Auth::user();
            $defaultOutlet = $user?->defaultOutlet();
            $outletId = $defaultOutlet?->id ?? $user?->outlets()->pluck('outlets.id')->first();
        }

        if (! $outletId) {
            abort(422, 'Outlet aktif belum dipilih.');
        }

        $data['outlet_id'] = $outletId;
        $data['updated_by'] = Auth::id();

        return $data;
    }
}
