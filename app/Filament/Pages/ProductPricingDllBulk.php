<?php

namespace App\Filament\Pages;

use App\Models\PricingDllSetting;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use UnitEnum;

class ProductPricingDllBulk extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Tabel DLL';

    public function mount(): void
    {
        $this->form->fill($this->defaultState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Tabel DLL Semua Produk')
                    ->schema([
                        TextInput::make('dll_100')
                            ->label('100 Gr')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('dll_200')
                            ->label('200 Gr')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('dll_500')
                            ->label('500 Gr')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('dll_1000')
                            ->label('1 Kg')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }

    public function submit(): void
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            abort(403);
        }

        $data = $this->form->getState();

        PricingDllSetting::updateOrCreate(
            ['outlet_id' => $outletId],
            [
                'dll_100' => $data['dll_100'] ?? null,
                'dll_200' => $data['dll_200'] ?? null,
                'dll_500' => $data['dll_500'] ?? null,
                'dll_1000' => $data['dll_1000'] ?? null,
            ]
        );

        Notification::make()
            ->title('Tabel DLL diperbarui')
            ->success()
            ->send();

        $this->form->fill($this->defaultState());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('submit')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment(Alignment::End)
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('submit'),
        ];
    }

    private function defaultState(): array
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            return [
                'dll_100' => null,
                'dll_200' => null,
                'dll_500' => null,
                'dll_1000' => null,
            ];
        }

        $setting = PricingDllSetting::where('outlet_id', $outletId)->first();

        return [
            'dll_100' => $setting?->dll_100,
            'dll_200' => $setting?->dll_200,
            'dll_500' => $setting?->dll_500,
            'dll_1000' => $setting?->dll_1000,
        ];
    }
}
