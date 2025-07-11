<?php

namespace App\Livewire\Pages\Medicines;

use Livewire\Component;
// use Filament\Actions\Action;
use App\Models\Medicine;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;

class MedicineCreate extends Component implements HasForms
{

    use InteractsWithForms;
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function resetForm() {
        $this->form->fill();
    }

    private function computeAndSetSku(callable $get, callable $set): void
    {
        $name = $get('name') ?? '';
        $potency = $get('potency') ?? '';
        $form = $get('form') ?? '';
        $packingQuantity = $get('packing_quantity') ?? '';
        $packingUnit = $get('packing_unit') ?? '';
        $formShort = $form ? substr($form, 0, 3) : '';
        $slugName = $name ? strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $name)) : '';
        // Map human-readable unit to SKU code
        $unitCode = Medicine::packingUnitCodeMap()[$packingUnit] ?? strtoupper($packingUnit);
        $sku = "{$slugName}-{$potency}-{$formShort}-{$packingQuantity}{$unitCode}";
        $set('sku', trim($sku, '-'));
        $set('sku', strtoupper(trim($sku, '-'))); // Capitalize here
    }

    

    public function submit()
    {
        $validated = $this->form->getState();

        Medicine::create($validated);

        Notification::make()
            ->title('Medicine Created')
            ->body('Medicines have been successfully created.')
            ->success()
            ->send();
    }
    public function formSubmit()
    {
        $this->submit();
            
        return $this->redirect(route('medicines.list'), navigate: true); // Update this route to your actual list page
    }
    
    public function submitAndCreate()
    {
        $this->submit();
        
        $this->form->fill(); // Reset the form for creating another medicine
        $this->dispatch('scroll-to-top');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Medicine Name')
                            ->required()
                            ->maxLength(255)
                            ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),

                        Group::make([
                            TextInput::make('barcode')
                                ->label('Barcode')
                                ->required()
                                ->unique(Medicine::class, 'barcode', ignoreRecord: true)
                                ->maxLength(255)
                                ->extraAttributes([
                                    'x-model' => 'barcode',
                                    'placeholder' => 'Enter or generate barcode',
                                ])
                                ->suffixAction(
                                    fn() =>
                                    Action::make('generateBarcode')
                                        ->icon('heroicon-m-sparkles')
                                        ->tooltip('Generate Barcode')
                                        ->action(function (\Filament\Forms\Set $set) {
                                            $set('barcode', 'MED' . rand(1000000000, 9999999999)); // 13-digit EAN-like
                                        })
                                )
                            // Add the button manually inside the Blade form or a custom component
                        ])->reactive()
                            // ->afterStateHydrated(function (TextInput $component, $state) {
                            //     $component->state($state);
                            // })
                            // ->afterStateUpdated(function (TextInput $component, $state) {
                            //     $component->state($state);
                            // })
                            ->extraAttributes(['x-data' => '{ barcode: $wire.entangle("data.barcode") }']),


                    ]),
                Section::make()
                    ->columns([
                        'sm' => 2,
                    ])
                    ->schema([
                        TextInput::make('potency')
                            ->label('Potency')
                            ->required()
                            ->maxLength(50)
                            ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),
                        Select::make('form')
                            ->options(Medicine::forms())
                            ->label('Form')
                            ->native(false)
                            ->searchable()
                            ->required()
                            ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),
                        TextInput::make('packing_quantity')
                            ->label('Packing Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->maxLength(10)
                            ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),
                        Select::make('packing_unit')
                            ->label('Unit')
                            ->required()
                            ->options(function () {
                                return collect(\App\Models\Medicine::packingUnits())
                                    ->flatten()
                                    ->unique()
                                    ->mapWithKeys(fn($unit) => [$unit => $unit])
                                    ->toArray();
                            })
                            ->native(false)
                            ->default(
                                fn($get) =>
                                \App\Models\Medicine::packingUnits()[$get('form')][0] ?? null
                            )
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),






                    ]),
                Section::make()
                    ->columns([
                        'sm' => 3,
                    ])
                    ->schema([
                        TextInput::make('purchase_price')
                            ->label('Purchase Price')
                            ->numeric()
                            ->default(0.00)
                            ->required(),
                        TextInput::make('selling_price')
                            ->label('Selling Price')
                            ->numeric()
                            ->default(0.00)
                            ->required(),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->disabled()
                            ->unique(Medicine::class, 'sku', ignoreRecord: true)
                            ->maxLength(255)
                            ->dehydrated(),

                    ]),
                Section::make()
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label('Description')
                            ->nullable()
                            ->columnSpanFull()
                            ->maxLength(5000),

                    ]),

            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-create');
    }
}
