<?php

namespace App\Livewire\Pages\Medicines\Concerns;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Tax;
use App\Models\Medicine;
use Filament\Forms\Form;
use App\Models\Manufacturer;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Symfony\Component\Intl\Countries;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;

trait HasMedicineForm
{
    public ?Medicine $cMedicine = null;
    public function setMedicine(Medicine $medicine): void
    {
        $this->cMedicine = $medicine;
        // dd($this->cMedicine);
    }
    public function computeAndSetSku(callable $get, callable $set): void
    {
        $name = $get('name') ?? '';
        $potency = $get('potency') ?? '';
        $form = $get('form') ?? '';
        $packingQuantity = $get('packing_quantity') ?? '';
        $packingUnit = $get('packing_unit') ?? '';
        $formShort = $form ? substr($form, 0, 3) : '';
        $slugName = $name ? strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $name)) : '';
        $unitCode = Medicine::packingUnitCodeMap()[$packingUnit] ?? strtoupper($packingUnit);
        $sku = "{$slugName}-{$potency}-{$formShort}-{$packingQuantity}{$unitCode}";
        $set('sku', strtoupper(trim($sku, '-')));
    }

    public function saveMedicine(): Medicine
    {
        $validated = $this->form->getState();
        dd($validated);

        $medicine = Medicine::create($validated);




        return $medicine;
    }

    public function medicineFormSchema(): array
    {
        return [
            Section::make()->columns(['sm' => 3])->schema([
                TextInput::make('name')
                    ->label('Medicine Name')
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),

                Group::make([
                    TextInput::make('barcode')
                        ->label('Barcode')
                        ->required()
                        ->rules([
                            Rule::unique('medicines', 'barcode')->ignore($this->cMedicine?->id ?? null),
                        ])
                        ->maxLength(255)
                        ->extraAttributes(['x-model' => 'barcode'])
                        ->suffixAction(
                            fn() =>
                            Action::make('generateBarcode')
                                ->icon('heroicon-m-sparkles')
                                ->tooltip('Generate Barcode')
                                ->action(function (Set $set) {
                                    $set('barcode', 'MED' . rand(1000000000, 9999999999));
                                })
                        )
                ])->reactive()
                    ->extraAttributes(['x-data' => '{ barcode: $wire.entangle("data.barcode") }']),
                Select::make('manufacturer_id')
                    ->label('Manufacturer')

                    ->options(fn() => Manufacturer::pluck('name', 'id')->toArray())
                    // ->default(fn($get) => $get['manufacturer'] ?? null)
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Group::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Manufacturer Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('contact_name')
                                    ->label('Contact Person')
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('address')
                                    ->label('Address')
                                    ->maxLength(255),

                                TextInput::make('website')
                                    ->label('Website')
                                    ->url()
                                    ->maxLength(255),

                                Select::make('country')
                                    ->label('Country')
                                    ->options(
                                        collect(Countries::getNames('en'))->sort()->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select Country')
                                    ->nullable(),

                                ToggleButtons::make('is_active')
                                    ->label('Active')
                                    ->boolean()
                                    ->inline()
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                    ])
            ]),

            Section::make()->columns(['sm' => 2])->schema([
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
                    ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),

                Select::make('packing_unit')
                    ->label('Unit')
                    ->required()
                    ->options(fn() => collect(Medicine::packingUnits())
                        ->flatten()
                        ->unique()
                        ->mapWithKeys(fn($unit) => [$unit => $unit])
                        ->toArray())
                    ->native(false)
                    ->default(fn($get) => Medicine::packingUnits()[$get('form')][0] ?? null)
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn($get, $set) => $this->computeAndSetSku($get, $set)),
            ]),

            Section::make()->columns(['sm' => 3])->schema([
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
                    ->rules([
                        Rule::unique('medicines', 'sku')->ignore($this->cMedicine?->id ?? null),
                    ])
                    ->dehydrated()
                    ->maxLength(255),
            ]),

            Section::make()->schema([
                RichEditor::make('description')
                    ->label('Description')
                    ->nullable()
                    ->columnSpanFull()
                    ->maxLength(5000),
            ]),
            Section::make()
                ->columns(['sm' => 3])
                ->schema([
                    Select::make('tax_id')
                        ->label('Tax')
                        ->options(fn() => Tax::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            Group::make()
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('rate')
                                        ->label('Rate')
                                        ->required()
                                        ->maxLength(255),



                                    ToggleButtons::make('is_active')
                                        ->label('Active')
                                        ->boolean()
                                        ->inline()
                                        ->default(true),
                                ])
                                ->columns(3)
                                ->columnSpanFull()
                        ]),
                    ToggleButtons::make('is_active')
                        ->label('Active?')
                        ->boolean()
                        ->grouped()
                        ->default(true)
                ]),

        ];
    }
}
