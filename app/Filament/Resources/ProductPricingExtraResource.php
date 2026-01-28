<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPricingExtraResource\Pages;
use App\Models\Product;
use App\Models\ProductPricingExtra;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use UnitEnum;

class ProductPricingExtraResource extends Resource
{
    protected static ?string $model = ProductPricingExtra::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Tabel Harga';

    protected static ?string $navigationLabel = 'Setting Harga';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Produk')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Nama Produk')
                            ->options(function (): array {
                                $outletId = OutletContext::id();
                                $query = Product::query()->orderBy('name');
                                if ($outletId) {
                                    $query->forOutlet($outletId);
                                }

                                return $query->pluck('name', 'id')->all();
                            })
                            ->searchable()
                            ->required()
                            ->rule(function ($record) {
                                $rule = Rule::unique('product_pricing_extras', 'product_id');
                                if ($record) {
                                    $rule->ignore($record->id);
                                }

                                return $rule;
                            }),
                    ]),
                Section::make('Tabel Modal')
                    ->schema([
                        Forms\Components\TextInput::make('modal_1kg')
                            ->label('Harga 1kg')
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                $value = is_numeric($state) ? (float) $state : null;
                                if ($value === null) {
                                    $set('modal_1gr', null);
                                    $set('modal_100', null);
                                    $set('modal_200', null);
                                    $set('modal_500', null);
                                    return;
                                }

                                $set('modal_1gr', round($value / 1000, 2));
                                $set('modal_100', round($value * 0.1, 2));
                                $set('modal_200', round($value * 0.2, 2));
                                $set('modal_500', round($value * 0.5, 2));
                            }),
                        Forms\Components\TextInput::make('modal_1gr')
                            ->label('Harga 1 Gr (auto)')
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('modal_100')
                            ->label('Harga 100 Gr (auto)')
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('modal_200')
                            ->label('Harga 200 Gr (auto)')
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('modal_500')
                            ->label('Harga 500 Gr (auto)')
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
            ->with('product')
            ->when($outletId, fn (Builder $query) => $query->whereHas('product.outlets', fn ($q) => $q->where('outlets.id', $outletId)));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductPricingExtras::route('/'),
            'create' => Pages\CreateProductPricingExtra::route('/create'),
            'edit' => Pages\EditProductPricingExtra::route('/{record}/edit'),
        ];
    }
}
