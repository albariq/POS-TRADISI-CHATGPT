<?php

namespace App\Filament\Resources\LabaRugiEntryResource\Pages;

use App\Filament\Resources\LabaRugiEntryResource;
use App\Models\User;
use App\Support\OutletContext;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLabaRugiEntry extends CreateRecord
{
    protected static string $resource = LabaRugiEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $outletId = OutletContext::id();
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
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }
}
