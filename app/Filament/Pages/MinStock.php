<?php

namespace App\Filament\Pages;

use App\Models\InventoryStock;
use App\Models\Product;
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

class MinStock extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-minus-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Stok Minimum';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Atur Stok Minimum')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(fn (): array => Product::forOutlet(OutletContext::id())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->live()
                            ->required(),
                        TextInput::make('min_qty_grams')
                            ->label('Minimum (gram)')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $product = Product::forOutlet(OutletContext::id())->findOrFail($data['product_id']);
        InventoryStock::updateOrCreate([
            'outlet_id' => OutletContext::id(),
            'product_id' => $product->id,
            'product_variant_id' => null,
        ], [
            'min_qty_grams' => (float) $data['min_qty_grams'],
        ]);

        Notification::make()
            ->title('Stok minimum diperbarui')
            ->success()
            ->send();

        $this->form->fill();
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
