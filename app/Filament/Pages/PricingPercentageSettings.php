<?php

namespace App\Filament\Pages;

use App\Models\PricingPercentageSetting;
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

class PricingPercentageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-percent-badge';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Setting Persentasi';

    public function mount(): void
    {
        $this->form->fill($this->defaultState());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Setting Persentasi')
                    ->schema([
                        TextInput::make('pct_100')
                            ->label('100 Gr')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('pct_200')
                            ->label('200 Gr')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('pct_500')
                            ->label('500 Gr')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('pct_1000')
                            ->label('1 Kg')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(4),
            ]);
    }

    public function submit(): void
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            abort(403);
        }

        $data = $this->form->getState();

        PricingPercentageSetting::updateOrCreate(
            ['outlet_id' => $outletId],
            [
                'pct_100' => $data['pct_100'] ?? null,
                'pct_200' => $data['pct_200'] ?? null,
                'pct_500' => $data['pct_500'] ?? null,
                'pct_1000' => $data['pct_1000'] ?? null,
            ]
        );

        Notification::make()
            ->title('Setting persentasi diperbarui')
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
                'pct_100' => null,
                'pct_200' => null,
                'pct_500' => null,
                'pct_1000' => null,
            ];
        }

        $setting = PricingPercentageSetting::where('outlet_id', $outletId)->first();

        return [
            'pct_100' => $setting?->pct_100,
            'pct_200' => $setting?->pct_200,
            'pct_500' => $setting?->pct_500,
            'pct_1000' => $setting?->pct_1000,
        ];
    }
}
