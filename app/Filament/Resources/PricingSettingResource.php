<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingSettingResource\Pages;
use App\Models\PricingSetting;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use UnitEnum;

class PricingSettingResource extends Resource
{
    protected static ?string $model = PricingSetting::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Setting Harga';

    public static function resolveOutletId(): ?int
    {
        $outletId = OutletContext::id();
        if ($outletId) {
            return $outletId;
        }

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('grams')
                    ->label('Ukuran')
                    ->options([
                        100 => '100 Gr',
                        200 => '200 Gr',
                        500 => '500 Gr',
                        1000 => '1 Kg',
                    ])
                    ->required()
                    ->rule(function ($record) {
                        $outletId = static::resolveOutletId();
                        $rule = Rule::unique('pricing_settings', 'grams')
                            ->where('outlet_id', $outletId);

                        if ($record) {
                            $rule->ignore($record->id);
                        }

                        return $rule;
                    }),
                Forms\Components\TextInput::make('packaging_cost')
                    ->label('Biaya Kemasan')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Forms\Components\TextInput::make('markup')
                    ->label('Markup (contoh 0.55)')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grams')
                    ->label('Ukuran')
                    ->formatStateUsing(fn ($state) => (int) $state === 1000 ? '1 Kg' : $state.' Gr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packaging_cost')
                    ->label('Biaya Kemasan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('markup')
                    ->label('Markup')
                    ->formatStateUsing(fn ($state) => number_format(((float) $state) * 100, 2).'%')
                    ->sortable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $outletId = static::resolveOutletId();

        return parent::getEloquentQuery()
            ->when($outletId, fn (Builder $query) => $query->where('outlet_id', $outletId))
            ->when(! $outletId, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPricingSettings::route('/'),
            'create' => Pages\CreatePricingSetting::route('/create'),
            'edit' => Pages\EditPricingSetting::route('/{record}/edit'),
        ];
    }
}
