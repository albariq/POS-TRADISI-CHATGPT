<?php

namespace App\Filament\Pages;

use App\Support\OutletContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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

class OutletSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Pengaturan Outlet';

    public function mount(): void
    {
        $outlet = OutletContext::outlet();

        $this->form->fill([
            'tax_rate' => $outlet?->tax_rate ?? 0,
            'service_charge_rate' => $outlet?->service_charge_rate ?? 0,
            'rounding_unit' => $outlet?->rounding_unit ?? 1,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Pajak & Service Charge')
                    ->schema([
                        TextInput::make('tax_rate')
                            ->label('Pajak (%)')
                            ->numeric()
                            ->required(),
                        TextInput::make('service_charge_rate')
                            ->label('Service Charge (%)')
                            ->numeric()
                            ->required(),
                        Select::make('rounding_unit')
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

    public function submit(): void
    {
        $data = $this->form->getState();
        $outlet = OutletContext::outlet();

        if (! $outlet) {
            Notification::make()
                ->title('Outlet tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        $outlet->update([
            'tax_rate' => (float) $data['tax_rate'],
            'service_charge_rate' => (float) $data['service_charge_rate'],
            'rounding_unit' => (int) $data['rounding_unit'],
        ]);

        Notification::make()
            ->title('Pengaturan disimpan')
            ->success()
            ->send();
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
}
