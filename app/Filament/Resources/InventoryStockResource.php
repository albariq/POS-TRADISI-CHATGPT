<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryStockResource\Pages;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Support\OutletContext;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;

class InventoryStockResource extends Resource
{
    protected static ?string $model = InventoryStock::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Stok';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Cabang')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty_grams')
                    ->label('Qty (g)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_qty_grams')
                    ->label('Min (g)')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('outlet')
                    ->form([
                        Select::make('outlet_id')
                            ->label('Cabang')
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

                                $options = ['all' => 'Semua Cabang'];
                                foreach ($outlets as $outlet) {
                                    $label = ($outlet->code ? $outlet->code.' - ' : '').$outlet->name;
                                    $options[(string) $outlet->id] = $label;
                                }

                                return $options;
                            })
                            ->default((string) (OutletContext::id() ?? 'all'))
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $outletId = $data['outlet_id'] ?? null;
                        if (! $outletId || $outletId === 'all') {
                            return $query;
                        }

                        return $query->where('outlet_id', (int) $outletId);
                    }),
            ])
            ->actions([])
            ->paginationPageOptions([20, 50, 100]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $allowedOutletIds = $user
            ? $user->outlets()->where('outlets.is_active', true)->pluck('outlets.id')->all()
            : Outlet::where('is_active', true)->pluck('id')->all();

        return parent::getEloquentQuery()
            ->whereIn('outlet_id', $allowedOutletIds)
            ->with(['outlet', 'product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryStocks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
