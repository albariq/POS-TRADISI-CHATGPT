<?php

namespace App\Filament\Pages;

use App\Models\InventoryStock;
use App\Support\OutletContext;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use UnitEnum;
use BackedEnum;

class InventoryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Inventory Report';

    public function getView(): string
    {
        return 'filament.pages.inventory-report';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InventoryStock::query()
                    ->where('outlet_id', OutletContext::id())
                    ->with('product', 'variant')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('qty_grams')
                    ->label('Qty (g)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_qty_grams')
                    ->label('Min (g)')
                    ->numeric()
                    ->sortable(),
            ]);
    }
}
