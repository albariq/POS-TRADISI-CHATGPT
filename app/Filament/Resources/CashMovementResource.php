<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashMovementResource\Pages;
use App\Models\CashMovement;
use App\Models\Shift;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use UnitEnum;

class CashMovementResource extends Resource
{
    protected static ?string $model = CashMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static string|UnitEnum|null $navigationGroup = 'KAS';

    protected static ?string $navigationLabel = 'Arus Kas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Arus Kas')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'in' => 'Masuk',
                                'out' => 'Keluar',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->label('Keterangan')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'in' ? 'Masuk' : 'Keluar')
                    ->color(fn ($state) => $state === 'in' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Keterangan')
                    ->limit(40),
                Tables\Columns\TextColumn::make('shift.opened_at')
                    ->label('Shift')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('User'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $outletId = OutletContext::id();

        return parent::getEloquentQuery()
            ->with(['shift', 'creator'])
            ->when($outletId, fn (Builder $query) => $query->where('outlet_id', $outletId))
            ->when(! $outletId, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $outletId = static::resolveOutletId();
        if (! $outletId) {
            abort(422, 'Outlet aktif belum dipilih.');
        }

        $data['outlet_id'] = $outletId;
        $data['created_by'] = Auth::id();
        $data['shift_id'] = Shift::where('outlet_id', $outletId)
            ->where('status', 'open')
            ->latest()
            ->value('id');
        if (! $data['shift_id']) {
            Log::warning('cash_movement_without_shift', [
                'outlet_id' => $outletId,
                'user_id' => Auth::id(),
                'type' => $data['type'] ?? null,
                'amount' => $data['amount'] ?? null,
            ]);
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['outlet_id'] = $data['outlet_id'] ?? static::resolveOutletId();

        return $data;
    }

    private static function resolveOutletId(): ?int
    {
        $outletId = OutletContext::id();
        if ($outletId) {
            return $outletId;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $defaultOutlet = $user->defaultOutlet();
        if ($defaultOutlet) {
            return $defaultOutlet->id;
        }

        return $user->outlets()->pluck('outlets.id')->first();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashMovements::route('/'),
            'create' => Pages\CreateCashMovement::route('/create'),
            'edit' => Pages\EditCashMovement::route('/{record}/edit'),
        ];
    }
}
