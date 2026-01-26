<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Outlet;
use App\Models\User;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use UnitEnum;
use BackedEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'penguna';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['OWNER', 'ADMIN']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Akun')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('app.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('app.email'))
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label(__('app.password'))
                            ->password()
                            ->confirmed()
                            ->minLength(6)
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(__('app.password_confirmation'))
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrated(false),
                        Forms\Components\Select::make('role')
                            ->label(__('app.role'))
                            ->options(fn () => Role::orderBy('name')->pluck('name', 'name')->all())
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('locale')
                            ->label(__('app.locale'))
                            ->options(['id' => 'id', 'en' => 'en'])
                            ->default('id'),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('app.active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make(__('app.outlets'))
                    ->schema([
                        Forms\Components\Select::make('outlet_ids')
                            ->label(__('app.outlets'))
                            ->options(fn () => Outlet::orderBy('name')->pluck('name', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('default_outlet_id')
                            ->label(__('app.default_outlet'))
                            ->options(function (Get $get) {
                                $outletIds = $get('outlet_ids') ?? [];
                                if (empty($outletIds)) {
                                    return [];
                                }

                                return Outlet::whereIn('id', $outletIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->disabled(fn (Get $get) => empty($get('outlet_ids')))
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('app.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('app.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('app.role'))
                    ->separator(', '),
                Tables\Columns\TextColumn::make('outlets.name')
                    ->label(__('app.outlets'))
                    ->separator(', '),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('app.status'))
                    ->boolean(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles', 'outlets']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
