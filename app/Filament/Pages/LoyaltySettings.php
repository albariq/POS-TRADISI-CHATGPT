<?php

namespace App\Filament\Pages;

use App\Models\LoyaltyRule;
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

class LoyaltySettings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Pelanggan';

    protected static ?string $navigationLabel = 'Aturan Poin';

    public function mount(): void
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            $this->form->fill();

            return;
        }

        $rule = LoyaltyRule::firstOrCreate(
            ['outlet_id' => $outletId],
            [
                'calculation_mode' => 'per_amount',
                'earn_rate_amount' => 10000,
                'earn_rate_points' => 1,
                'redeem_rate_amount' => null,
            ]
        );

        $this->form->fill([
            'calculation_mode' => $rule->calculation_mode ?? 'per_amount',
            'earn_rate_amount' => $rule->earn_rate_amount,
            'earn_rate_points' => $rule->earn_rate_points,
            'redeem_rate_amount' => $rule->redeem_rate_amount,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Aturan Perhitungan Poin')
                    ->schema([
                        Select::make('calculation_mode')
                            ->label('Metode Perhitungan')
                            ->options([
                                'per_amount' => 'Per nominal belanja',
                                'per_transaction' => 'Per transaksi',
                            ])
                            ->default('per_amount')
                            ->required()
                            ->native(false),
                        TextInput::make('earn_rate_amount')
                            ->label('Nominal dasar (Rp)')
                            ->helperText('Per nominal: poin diberikan setiap kelipatan nominal ini. Per transaksi: menjadi minimal belanja untuk dapat poin.')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('earn_rate_points')
                            ->label('Poin yang diberikan')
                            ->helperText('Per nominal: poin per kelipatan nominal dasar. Per transaksi: poin per transaksi (jika memenuhi minimal belanja).')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('redeem_rate_amount')
                            ->label('Nilai tukar poin (Rp)')
                            ->helperText('Opsional. Misal 1 poin = Rp 1000.')
                            ->numeric()
                            ->nullable()
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public function submit(): void
    {
        $outletId = OutletContext::id();
        if (! $outletId) {
            return;
        }

        $data = $this->form->getState();

        LoyaltyRule::updateOrCreate(
            ['outlet_id' => $outletId],
            [
                'calculation_mode' => $data['calculation_mode'] ?? 'per_amount',
                'earn_rate_amount' => (int) ($data['earn_rate_amount'] ?? 0),
                'earn_rate_points' => (int) ($data['earn_rate_points'] ?? 0),
                'redeem_rate_amount' => isset($data['redeem_rate_amount'])
                    ? (int) $data['redeem_rate_amount']
                    : null,
            ]
        );

        Notification::make()
            ->title('Aturan poin diperbarui')
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

