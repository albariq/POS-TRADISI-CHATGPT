<?php

namespace App\Filament\Resources\CashMovementResource\Pages;

use App\Filament\Resources\CashMovementResource;
use App\Models\Shift;
use App\Support\OutletContext;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;

class CreateCashMovement extends CreateRecord
{
    protected static string $resource = CashMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            abort(422, 'Outlet aktif belum dipilih.');
        }

        $shiftId = Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->latest()
            ->value('id');
        if (! $shiftId) {
            abort(422, 'Tidak ada shift aktif.');
        }

        $data['outlet_id'] = $outletId;
        $data['shift_id'] = $shiftId;
        $data['created_by'] = Auth::id();

        return $data;
    }
}
