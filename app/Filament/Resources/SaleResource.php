<?php

namespace App\Filament\Resources;

use App\Exports\SalesDetailExport;
use App\Filament\Resources\SaleResource\Pages;
use App\Models\Outlet;
use App\Models\Sale;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-refund';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Detail Penjualan';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Transaksi')
                    ->schema([
                        TextEntry::make('receipt_number')
                            ->label('No. Struk'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'paid' => 'success',
                                'draft' => 'warning',
                                'void' => 'danger',
                                'refunded' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => static::formatStatus($state)),
                        TextEntry::make('outlet.name')
                            ->label('Cabang'),
                        TextEntry::make('cashier.name')
                            ->label('Kasir'),
                        TextEntry::make('customer.name')
                            ->label('Pelanggan'),
                        TextEntry::make('created_at')
                            ->label('Tanggal')
                            ->dateTime('d-m-Y H:i'),
                        TextEntry::make('paid_at')
                            ->label('Waktu Bayar')
                            ->dateTime('d-m-Y H:i'),
                        TextEntry::make('subtotal')
                            ->money('IDR', true),
                        TextEntry::make('discount_total')
                            ->label('Diskon')
                            ->money('IDR', true),
                        TextEntry::make('net_sales')
                            ->label('Net Sales')
                            ->state(fn (Sale $record): float => max(0, (float) $record->subtotal - (float) $record->discount_total))
                            ->money('IDR', true),
                        TextEntry::make('tax_total')
                            ->label('Pajak')
                            ->money('IDR', true),
                        TextEntry::make('service_total')
                            ->label('Service')
                            ->money('IDR', true),
                        TextEntry::make('rounding_adjustment')
                            ->label('Pembulatan')
                            ->money('IDR', true),
                        TextEntry::make('grand_total')
                            ->label('Total')
                            ->money('IDR', true),
                        TextEntry::make('gross_profit')
                            ->label('Gross Profit')
                            ->state(function (Sale $record): float {
                                $netSales = max(0, (float) $record->subtotal - (float) $record->discount_total);
                                $cogsTotal = (float) ($record->cogs_total ?? $record->items->sum('cogs_total'));
                                return $netSales - $cogsTotal;
                            })
                            ->money('IDR', true),
                        TextEntry::make('gross_margin')
                            ->label('Gross Margin')
                            ->state(function (Sale $record): float {
                                $netSales = max(0, (float) $record->subtotal - (float) $record->discount_total);
                                if ($netSales <= 0) {
                                    return 0;
                                }
                                $cogsTotal = (float) ($record->cogs_total ?? $record->items->sum('cogs_total'));
                                return round((($netSales - $cogsTotal) / $netSales) * 100, 2);
                            })
                            ->suffix('%'),
                        TextEntry::make('void_reason')
                            ->label('Alasan Void'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                Section::make('Rincian Item')
                    ->schema([
                        TextEntry::make('items_detail')
                            ->label('Item')
                            ->state(fn (Sale $record): array => $record->items->map(fn ($item) => static::formatItemLine($item))->all())
                            ->listWithLineBreaks()
                            ->columnSpanFull(),
                    ]),
                Section::make('Pembayaran')
                    ->schema([
                        TextEntry::make('payments_detail')
                            ->label('Metode Bayar')
                            ->state(fn (Sale $record): array => $record->payments->map(fn ($payment) => static::formatPaymentLine($payment))->all())
                            ->listWithLineBreaks()
                            ->columnSpanFull(),
                        TextEntry::make('payments_total')
                            ->label('Total Dibayar')
                            ->state(fn (Sale $record): float => (float) $record->payments->sum('amount'))
                            ->money('IDR', true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('No. Struk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'paid' => 'success',
                        'draft' => 'warning',
                        'void' => 'danger',
                        'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => static::formatStatus($state)),
                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Cabang')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Item')
                    ->state(fn (Sale $record): array => $record->items->map(fn ($item) => static::formatItemSummary($item))->all())
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payments_summary')
                    ->label('Metode Bayar')
                    ->state(fn (Sale $record): array => $record->payments->map(fn ($payment) => static::formatPaymentLine($payment))->all())
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('discount_total')
                    ->label('Diskon')
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('net_sales')
                    ->label('Net Sales')
                    ->state(fn (Sale $record): float => max(0, (float) $record->subtotal - (float) $record->discount_total))
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tax_total')
                    ->label('Pajak')
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('service_total')
                    ->label('Service')
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rounding_adjustment')
                    ->label('Pembulatan')
                    ->money('IDR', true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d-m-Y H:i')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('outlet')
                    ->form([
                        Select::make('outlet_id')
                            ->label('Cabang')
                            ->options(fn (): array => static::getOutletOptions())
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'paid' => 'Dibayar',
                        'void' => 'Void',
                        'refunded' => 'Refund',
                    ])
                    ->multiple()
                    ->searchable(),
                SelectFilter::make('cashier_id')
                    ->label('Kasir')
                    ->relationship(
                        'cashier',
                        'name',
                        fn (Builder $query) => $query
                            ->whereHas('outlets', fn (Builder $query) => $query->whereIn('outlets.id', static::getAllowedOutletIds()))
                            ->orderBy('name')
                    )
                    ->searchable(),
                SelectFilter::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship(
                        'customer',
                        'name',
                        fn (Builder $query) => $query
                            ->whereIn('outlet_id', static::getAllowedOutletIds())
                            ->orderBy('name')
                    )
                    ->searchable(),
                Tables\Filters\Filter::make('payment_method')
                    ->form([
                        Select::make('method')
                            ->label('Metode Bayar')
                            ->options([
                                'cash' => 'Tunai',
                                'card' => 'Kartu',
                                'qris' => 'QRIS',
                                'ewallet' => 'E-Wallet',
                                'transfer' => 'Transfer',
                            ])
                            ->multiple()
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $methods = $data['method'] ?? null;
                        if (blank($methods)) {
                            return $query;
                        }

                        return $query->whereHas('payments', fn (Builder $query) => $query->whereIn('method', $methods));
                    }),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
            ])
            ->toolbarActions([
                Actions\Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getTableQueryForExport();

                        return Excel::download(new SalesDetailExport($query), 'sales-detail.xlsx');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([20, 50, 100]);
    }

    public static function getEloquentQuery(): Builder
    {
        $allowedOutletIds = static::getAllowedOutletIds();

        return parent::getEloquentQuery()
            ->when(
                empty($allowedOutletIds),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
                fn (Builder $query) => $query->whereIn('outlet_id', $allowedOutletIds)
            )
            ->with(['outlet', 'cashier', 'customer', 'payments', 'items.product'])
            ->withSum('items as cogs_total', 'cogs_total');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'view' => Pages\ViewSale::route('/{record}'),
        ];
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

    private static function getAllowedOutletIds(): array
    {
        $user = Auth::user();

        return $user
            ? $user->outlets()->where('outlets.is_active', true)->pluck('outlets.id')->all()
            : Outlet::where('is_active', true)->pluck('id')->all();
    }

    private static function getOutletOptions(): array
    {
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
    }

    private static function formatStatus(?string $status): string
    {
        return match ($status) {
            'paid' => 'Dibayar',
            'draft' => 'Draft',
            'void' => 'Void',
            'refunded' => 'Refund',
            default => $status ?? '-',
        };
    }

    private static function formatItemSummary($item): string
    {
        $name = $item->name_snapshot ?: ($item->product?->name ?? '-');
        $qty = number_format((float) $item->qty, 0, ',', '.');

        return sprintf('%s x %s', $name, $qty);
    }

    private static function formatItemLine($item): string
    {
        $name = $item->name_snapshot ?: ($item->product?->name ?? '-');
        $qty = number_format((float) $item->qty, 0, ',', '.');
        $unitPrice = number_format((float) $item->unit_price, 0, ',', '.');
        $lineTotal = number_format((float) $item->line_total, 0, ',', '.');

        return sprintf('%s x %s @ Rp %s = Rp %s', $name, $qty, $unitPrice, $lineTotal);
    }

    private static function formatPaymentLine($payment): string
    {
        $amount = number_format((float) $payment->amount, 0, ',', '.');
        $change = number_format((float) $payment->change_amount, 0, ',', '.');
        $method = match ($payment->method) {
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
            'transfer' => 'Transfer',
            default => $payment->method ?? '-',
        };

        if ((float) $payment->change_amount > 0) {
            return sprintf('%s Rp %s (Kembali Rp %s)', $method, $amount, $change);
        }

        return sprintf('%s Rp %s', $method, $amount);
    }
}
