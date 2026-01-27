<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Purchase;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Pembelian';

    protected static ?string $navigationLabel = 'Pembelian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pembelian')
                    ->schema([
                        Forms\Components\Select::make('outlet_id')
                            ->label('Cabang Tujuan')
                            ->options(function (): array {
                                $user = Auth::user();
                                $outlets = $user
                                    ? $user->outlets()
                                        ->where('outlets.is_active', true)
                                        ->orderBy('outlets.name')
                                        ->get(['outlets.id as id', 'outlets.code as code', 'outlets.name as name'])
                                    : Outlet::where('is_active', true)
                                        ->orderBy('name')
                                        ->get(['id', 'code', 'name']);

                                $options = [];
                                foreach ($outlets as $outlet) {
                                    $label = ($outlet->code ? $outlet->code.' - ' : '').$outlet->name;
                                    $options[$outlet->id] = $label;
                                }

                                return $options;
                            })
                            ->default(OutletContext::id())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('items', [])),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->maxLength(100)
                            ->nullable(),
                        Forms\Components\TextInput::make('supplier_name')
                            ->label('Pemasok')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('purchased_at')
                            ->label('Tanggal Pembelian')
                            ->nullable(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Item')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options(fn (Get $get): array => Product::where('outlet_id', $get('../../outlet_id') ?? OutletContext::id())
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->searchable()
                                    ->live()
                                    ->required(),
                                Forms\Components\TextInput::make('qty_grams')
                                    ->label('Qty (gram)')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('unit_cost')
                                    ->label('Cost / kg')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(3)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Pemasok')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Cabang')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Item')
                    ->state(fn (Purchase $record): array => $record->items
                        ->map(fn ($item) => sprintf('%s (%s g)', $item->product?->name ?? '-', number_format($item->qty_grams ?? 0, 0, ',', '.')))
                        ->all())
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('purchased_at')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('outlet_id', OutletContext::id())
            ->with(['items.product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }
}
