<?php

namespace App\Livewire\Pages\Medicines\Concerns;

use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Tax;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
// use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Intl\Countries;

// use Filament\Schemas\Components\Section;
// use Filament\Schemas\Components\Group;
// use Filament\Actions\Action;
// use Filament\Schemas\Components\Utilities\Set;
// use App\Models\Tax;
// use App\Models\Medicine;
// use Filament\Forms\Form;
// use App\Models\Manufacturer;
// use Illuminate\Validation\Rule;
// use Filament\Forms\Components\Grid;
// use Filament\Forms\Components\Select;
// use Filament\Forms\Components\Toggle;
// use Symfony\Component\Intl\Countries;
// use Filament\Forms\Components\TextInput;
// use Filament\Forms\Components\ToggleButtons;
// use Filament\Forms\Components\MarkdownEditor;
// use Filament\Forms\Components\RichEditor;

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
        $potency = $get('potency') ? $get('potency') . '-' : '';
        $form = $get('form') ?? '';
        $packingQuantity = $get('packing_quantity') ?? '';
        $packingUnit = $get('packing_unit') ?? '';
        $formShort = $form ? substr($form, 0, 3) : '';
        $slugName = $name ? strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $name)) : '';
        $unitCode = Medicine::packingUnitCodeMap()[$packingUnit] ?? strtoupper($packingUnit);
        $sku = "{$slugName}-{$potency}{$formShort}-{$packingQuantity}{$unitCode}";
        $set('sku', strtoupper(trim($sku, '-')));
    }

    public function saveMedicine(): Medicine
    {
        $validated = $this->form->getState();
        // dd($validated);

        $medicine = Medicine::create($validated);

        return $medicine;
    }

    public function medicineFormSchema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['sm' => 3])->schema([
                TextInput::make('name')
                    ->label('Medicine Name')
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated(fn ($get, $set) => $this->computeAndSetSku($get, $set)),

                Group::make([
                    TextInput::make('barcode')
                        ->label('Barcode')
                        ->required()
                        ->rules([
                            Rule::unique('medicines', 'barcode')->ignore($this->cMedicine?->id ?? null),
                        ])
                        ->maxLength(255)
                        // ->extraAttributes(['x-model' => 'barcode'])
                        ->suffixAction(function () {
                            return \Filament\Actions\Action::make('generateBarcode')
                                ->icon('heroicon-m-sparkles')
                                ->tooltip('Generate Barcode')
                                ->action(function (\Filament\Schemas\Components\Utilities\Set $set) {
                                    // dd($set);
                                    $set('barcode', rand(1000000000, 9999999999));
                                });
                        }),
                ])->live(debounce: 500),
                // ->extraAttributes(['x-data' => '{ barcode: $wire.entangle("data.barcode") }']),
                Select::make('manufacturer_id')
                    ->label('Manufacturer')

                    ->options(fn () => Manufacturer::pluck('name', 'id')->toArray())
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
                            ->columnSpanFull(),
                    ])
                    ->createOptionAction(function (Action $action) {
                        return $action
                            ->modalHeading('Create manufaturer')
                            ->modalSubmitActionLabel('Create manufaturer')
                            ->modalWidth('xl');
                    })
                    ->createOptionUsing(function (array $data) {
                        // Server-side validation
                        $validator = Validator::make($data, [
                            'name' => ['required', 'string', 'max:255', Rule::unique('manufacturers', 'name')],
                            'contact_name' => ['nullable', 'string', 'max:255'],
                            'phone' => ['nullable', 'string', 'max:20'],
                            'email' => ['nullable', 'email', 'max:255', Rule::unique('manufacturers', 'email')],
                            'address' => ['nullable', 'string', 'max:255'],
                            'website' => ['nullable', 'url', 'max:255'],
                            'country' => ['nullable', 'string', 'max:8'], // adjust if you store ISO codes
                            'is_active' => ['nullable', 'boolean'],
                        ]);

                        // This will throw ValidationException automatically and Filament will show errors.
                        $validated = $validator->validate();

                        // Optional: normalize phone (strip non-digits, etc.)
                        if (! empty($validated['phone'])) {
                            $validated['phone'] = preg_replace('/\D+/', '', $validated['phone']);
                        }

                        // Create the model
                        $manufacturer = Manufacturer::create([
                            'name' => $validated['name'],
                            'contact_name' => $validated['contact_name'] ?? null,
                            'phone' => $validated['phone'] ?? null,
                            'email' => $validated['email'] ?? null,
                            'address' => $validated['address'] ?? null,
                            'website' => $validated['website'] ?? null,
                            'country' => $validated['country'] ?? null,
                            'is_active' => $validated['is_active'] ?? true,
                        ]);

                        return $manufacturer->id;
                    }),
            ]),

            Section::make()->columns(['sm' => 2])->schema([
                TextInput::make('potency')
                    ->label('Potency')
                    // ->required()
                    ->maxLength(50)
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $this->computeAndSetSku($get, $set)),

                Select::make('form')
                    ->options(Medicine::forms())
                    ->label('Form')
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $this->computeAndSetSku($get, $set)),

                TextInput::make('packing_quantity')
                    ->label('Packing Quantity')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $this->computeAndSetSku($get, $set)),

                Select::make('packing_unit')
                    ->label('Unit')
                    ->required()
                    ->options(fn () => collect(Medicine::packingUnits())
                        ->flatten()
                        ->unique()
                        ->mapWithKeys(fn ($unit) => [$unit => $unit])
                        ->toArray())
                    ->native(false)
                    ->default(fn ($get) => Medicine::packingUnits()[$get('form')][0] ?? null)
                    ->searchable()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $this->computeAndSetSku($get, $set)),
            ]),

            Section::make()->columns(['sm' => 4])->schema([
                TextInput::make('purchase_price')
                    ->label('Purchase Price')
                    ->numeric()
                    ->default(0.00)
                    ->required(),
                TextInput::make('margin')
                    ->label('Margin (%)')
                    ->numeric()
                    ->default(0.00)
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function($get, $set){
                        $purchase = (float) ($get('purchase_price') ?? 0);
                        // only calculate when purchase price is set and non-zero
                        if ($purchase <= 0) {
                            return;
                        }
                        $margin = (float) ($get('margin') ?? 0);
                        // sale = purchase * (1 + margin/100)
                        $sale = $purchase * (1 + ($margin / 100));
                        // set rounded sale price
                        $set('sale_price', round($sale, 2));
                    }),
                Select::make('tax_id')
                    ->label('Tax')
                    ->options(fn () => Tax::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        Group::make()
                            ->columns(['xs' => 1])
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
                            ->columnSpanFull(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        // Server-side validation
                        $validator = Validator::make($data, [
                            'name' => ['required', 'string', 'max:255', Rule::unique('manufacturers', 'name')],
                            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
                            'is_active' => ['nullable', 'boolean'],
                        ]);

                        // This will throw ValidationException automatically and Filament will show errors.
                        $validated = $validator->validate();

                        $tax = Tax::create([
                            'name' => $validated['name'],
                            'rate' => $validated['rate'] ?? null,
                            'is_active' => $validated['is_active'] ?? true,
                        ]);

                        return $tax->id;
                    })
                    ->createOptionAction(function (Action $action) {
                        return $action
                            ->modalHeading('Create tax')
                            ->modalSubmitActionLabel('Create tax')
                            ->modalWidth('xl');
                    }),
                ToggleButtons::make('is_tax_inclusive')
                    ->label('Tax Included?')
                    ->boolean()
                    ->grouped()
                    ->default(true)
                    ->columns(2),
                TextInput::make('sale_price')
                    ->label('Sale Price')
                    ->numeric()
                    ->live(debounce: 500)
                    ->default(0.00)
                    ->required()
                    ->afterStateUpdated(function($get, $set){
                       $purchase = (float) ($get('purchase_price') ?? 0);
                        if ($purchase <= 0) {
                            return;
                        }
                        $sale = (float) ($get('sale_price') ?? 0);
                        // margin = ((sale - purchase) / purchase) * 100
                        $margin = $purchase > 0 ? (($sale - $purchase) / $purchase) * 100 : 0;
                        $set('margin', round($margin, 2));
                    }),
                TextInput::make('discount_on_sale')
                    ->label('Discount on Sale')
                    ->numeric()
                    ->default(0.00)
                    ->required(),
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

                    TextInput::make('sku')
                        ->label('SKU')
                        ->disabled()
                        ->rules([
                            Rule::unique('medicines', 'sku')->ignore($this->cMedicine?->id ?? null),
                        ])
                        ->dehydrated()
                        ->maxLength(255),
                    ToggleButtons::make('is_active')
                        ->label('Active?')
                        ->boolean()
                        ->grouped()
                        ->default(true),
                ]),

        ]);
    }
}
