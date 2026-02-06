<?php

namespace App\Filament\Resources\CashMovementResource\Pages;

use App\Filament\Resources\CashMovementResource;
use App\Models\CashMovement;
use App\Models\Shift;
use App\Support\OutletContext;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            Log::warning('cash_movement_without_shift', [
                'outlet_id' => $outletId,
                'user_id' => Auth::id(),
                'type' => $data['type'] ?? null,
                'amount' => $data['amount'] ?? null,
            ]);
        }

        $data['outlet_id'] = $outletId;
        $data['shift_id'] = $shiftId;
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->record instanceof CashMovement) {
            return;
        }

        if ($this->record->shift_id === null) {
            AuditLogger::log(
                'cash_movement_no_shift',
                CashMovement::class,
                $this->record->id,
                null,
                $this->record->toArray(),
                $this->record->outlet_id
            );
        }
    }
}
