<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Models\Outlet;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Cabang';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Cabang')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telepon')
                            ->maxLength(50)
                            ->nullable(),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('currency_code')
                            ->label('Mata Uang')
                            ->required()
                            ->maxLength(3)
                            ->default('IDR'),
                        Forms\Components\TextInput::make('timezone')
                            ->label('Zona Waktu')
                            ->required()
                            ->maxLength(100)
                            ->default('Asia/Jakarta'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Pajak & Pembulatan')
                    ->schema([
                        Forms\Components\TextInput::make('tax_rate')
                            ->label('Pajak (%)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('service_charge_rate')
                            ->label('Service Charge (%)')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('rounding_unit')
                            ->label('Satuan Pembulatan')
                            ->options([
                                1 => '1',
                                10 => '10',
                                100 => '100',
                                1000 => '1000',
                            ])
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('timezone')
                    ->label('Zona Waktu')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }
}
