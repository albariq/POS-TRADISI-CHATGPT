<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LabaRugiEntryResource;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class LabaRugi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'KAS';

    protected static ?string $navigationLabel = 'Laba Rugi';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.laba-rugi';

    public function mount(): void
    {
        $this->redirect(LabaRugiEntryResource::getUrl('index'));
    }
}
