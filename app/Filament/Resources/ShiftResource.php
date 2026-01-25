<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use App\Support\OutletContext;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Operasional';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('opened_at')
                    ->label('Dibuka')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Ditutup')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('opener.name')
                    ->label('Dibuka Oleh')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Kas Awal')
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('closing_balance_actual')
                    ->label('Kas Akhir')
                    ->money('IDR', true)
                    ->toggleable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('outlet_id', OutletContext::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
