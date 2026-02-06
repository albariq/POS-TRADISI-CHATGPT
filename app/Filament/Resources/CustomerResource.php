<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Support\OutletContext;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Pelanggan';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER', 'CASHIER']) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        return $user?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER']) ?? false;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();

        return $user?->hasAnyRole(['OWNER', 'ADMIN', 'MANAGER']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('phone')
                    ->maxLength(50)
                    ->nullable(),
                Forms\Components\Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('points_balance')
                    ->numeric()
                    ->default(0)
                    ->disabled(fn () => Auth::user()?->hasRole('CASHIER') ?? false),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->disabled(fn () => Auth::user()?->hasRole('CASHIER') ?? false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('points_balance')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->recordActions([
                Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => static::canDelete($record)),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('outlet_id', OutletContext::id());
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user?->hasRole('CASHIER')) {
            $data['points_balance'] = 0;
            $data['is_active'] = true;
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
