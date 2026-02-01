<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabaRugiEntryResource\Pages;
use App\Filament\Resources\LabaRugiEntryResource\Widgets\LabaRugiSummary;
use App\Models\LabaRugiEntry;
use App\Models\User;
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
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LabaRugiEntryResource extends Resource
{
    protected static ?string $model = LabaRugiEntry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'KAS';

    protected static ?string $navigationLabel = 'Laba Rugi';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER']) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Laba Rugi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis')
                            ->options([
                                'pendapatan' => 'Pendapatan',
                                'biaya' => 'Biaya',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->maxLength(100)
                            ->required(),
                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'pendapatan' ? 'Pendapatan' : 'Biaya')
                    ->color(fn (string $state) => $state === 'pendapatan' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(40),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('User'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'pendapatan' => 'Pendapatan',
                        'biaya' => 'Biaya',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('tanggal', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('tanggal', '<=', $date)
                            );
                    }),
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
            ->with(['creator'])
            ->when($outletId, fn (Builder $query) => $query->where('outlet_id', $outletId))
            ->when(! $outletId, fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $outletId = static::resolveOutletId();
        if (! $outletId) {
            abort(422, 'Outlet aktif belum dipilih.');
        }

        $data['outlet_id'] = $outletId;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['outlet_id'] = $data['outlet_id'] ?? static::resolveOutletId();
        $data['updated_by'] = Auth::id();

        return $data;
    }

    private static function resolveOutletId(): ?int
    {
        $outletId = OutletContext::id();
        if ($outletId) {
            return $outletId;
        }

        /** @var User|null $user */
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

    public static function getWidgets(): array
    {
        return [
            LabaRugiSummary::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabaRugiEntries::route('/'),
            'create' => Pages\CreateLabaRugiEntry::route('/create'),
            'edit' => Pages\EditLabaRugiEntry::route('/{record}/edit'),
        ];
    }
}
