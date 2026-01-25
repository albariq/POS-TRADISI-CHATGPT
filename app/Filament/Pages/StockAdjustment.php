<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockService;
use App\Support\OutletContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use UnitEnum;

class StockAdjustment extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Penyesuaian Stok';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Penyesuaian Stok')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(fn (): array => Product::where('outlet_id', OutletContext::id())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->live()
                            ->required(),
                        Select::make('product_variant_id')
                            ->label('Variant')
                            ->options(fn (Get $get): array => ProductVariant::where('product_id', $get('product_id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->nullable()
                            ->disabled(fn (Get $get): bool => blank($get('product_id'))),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'in' => 'Stok Masuk',
                                'out' => 'Stok Keluar',
                                'adjust' => 'Penyesuaian',
                            ])
                            ->required(),
                        TextInput::make('qty_grams')
                            ->label('Qty (gram)')
                            ->numeric()
                            ->required(),
                        TextInput::make('reason')
                            ->label('Alasan / Referensi')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $product = Product::where('outlet_id', OutletContext::id())->findOrFail($data['product_id']);
        $variantId = $data['product_variant_id'] ?? null;

        if ($variantId) {
            ProductVariant::where('product_id', $product->id)->findOrFail($variantId);
        }

        $qty = (float) $data['qty_grams'];
        $delta = $data['type'] === 'out' ? -1 * abs($qty) : abs($qty);
        if ($data['type'] === 'adjust') {
            $delta = $qty;
        }

        app(StockService::class)->adjust(
            $product->id,
            $variantId,
            $delta,
            $data['type'],
            $data['reason'] ?? null
        );

        Notification::make()
            ->title('Stok berhasil diperbarui')
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
            Action::make('apply')
                ->label('Terapkan')
                ->submit('submit'),
        ];
    }
}
