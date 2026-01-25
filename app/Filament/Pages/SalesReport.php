<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use App\Support\OutletContext;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Sales Report';

    public function getView(): string
    {
        return 'filament.pages.sales-report';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('outlet_id', OutletContext::id())
                    ->where('status', 'paid')
            )
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_total')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
