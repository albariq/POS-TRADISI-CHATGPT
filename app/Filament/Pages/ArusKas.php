<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class ArusKas extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static string|UnitEnum|null $navigationGroup = 'KAS';

    protected static ?string $navigationLabel = 'Arus Kas';

    protected string $view = 'filament.pages.arus-kas';
}
