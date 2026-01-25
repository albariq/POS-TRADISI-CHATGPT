<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                                    ->options(fn (): array => \App\Models\Product::where('outlet_id', OutletContext::id())
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->searchable()
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
