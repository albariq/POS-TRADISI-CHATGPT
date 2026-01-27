<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Tag;
use App\Support\OutletContext;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Katalog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Produk')
                    ->schema([
                        Forms\Components\Select::make('outlet_ids')
                            ->label('Outlet')
                            ->options(fn (): array => static::getAllowedOutletOptions())
                            ->default(function (?Product $record): array {
                                if ($record) {
                                    return $record->outlets()->pluck('outlets.id')->all();
                                }

                                return OutletContext::id() ? [OutletContext::id()] : [];
                            })
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Pilih satu atau beberapa outlet untuk produk ini.'),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('base_price')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(function (Get $get): array {
                                $outletIds = $get('outlet_ids') ?? [];
                                $outletId = (int) (is_array($outletIds) ? ($outletIds[0] ?? 0) : $outletIds);
                                if (! $outletId) {
                                    $outletId = (int) (OutletContext::id() ?? 0);
                                }

                                if (! $outletId) {
                                    return [];
                                }

                                return Category::query()
                                    ->where('outlet_id', $outletId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('tags')
                            ->label('Tags')
                            ->options(function (Get $get): array {
                                $outletIds = $get('outlet_ids') ?? [];
                                $outletId = (int) (is_array($outletIds) ? ($outletIds[0] ?? 0) : $outletIds);
                                if (! $outletId) {
                                    $outletId = (int) (OutletContext::id() ?? 0);
                                }

                                if (! $outletId) {
                                    return [];
                                }

                                return Tag::query()
                                    ->where('outlet_id', $outletId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->default(fn (?Product $record): array => $record?->tags->pluck('id')->all() ?? [])
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Varian')
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sku')
                                    ->maxLength(100)
                                    ->nullable(),
                                Forms\Components\TextInput::make('price_override')
                                    ->numeric()
                                    ->nullable(),
                                Forms\Components\TextInput::make('cost_price')
                                    ->numeric()
                                    ->nullable(),
                                Forms\Components\TextInput::make('grams_per_unit')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forOutlet(OutletContext::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * Limit outlet options to outlets assigned to the current user.
     *
     * @return array<int, string>
     */
    protected static function getAllowedOutletOptions(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $allowedOutletIds = $user->outlets()->pluck('outlets.id')->all();

        if (empty($allowedOutletIds)) {
            return [];
        }

        return Outlet::query()
            ->whereIn('id', $allowedOutletIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
