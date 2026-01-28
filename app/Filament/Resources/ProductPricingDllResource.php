<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPricingDllResource\Pages;
use App\Models\Product;
use App\Models\ProductPricingDll;
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
use Illuminate\Validation\Rule;
use UnitEnum;

class ProductPricingDllResource extends Resource
{
    protected static ?string $model = ProductPricingDll::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Tabel DLL';

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
                                $rule = Rule::unique('product_pricing_dlls', 'product_id');
                                if ($record) {
                                    $rule->ignore($record->id);
                                }

                                return $rule;
                            }),
                    ]),
                Section::make('Tabel DLL')
                    ->schema([
                        Forms\Components\TextInput::make('dll_100')
                            ->label('100 Gr')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('dll_200')
                            ->label('200 Gr')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('dll_500')
                            ->label('500 Gr')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('dll_1000')
                            ->label('1 Kg')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(4),
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
            'index' => Pages\ListProductPricingDlls::route('/'),
            'create' => Pages\CreateProductPricingDll::route('/create'),
            'edit' => Pages\EditProductPricingDll::route('/{record}/edit'),
        ];
    }
}
