<?php

namespace App\Forms\Schemas;

use App\Models\Tax;
use App\Models\Medicine;
use App\Models\Manufacturer;
use Filament\Actions\Action;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Intl\Countries;

class MedicineFormSchema
{
    public static function schema($livewire = null): array
    {
        return [
            Section::make()->columns(['sm' => 3])->schema([
                TextInput::make('name')
                    ->label('Medicine Name')
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated(fn ($get, $set) => $livewire ? $livewire->computeAndSetSku($get, $set) : null),

                Group::make([
                    TextInput::make('barcode')
                        ->label('Barcode')
                        ->required()
                        ->rules([
                            Rule::unique('medicines', 'barcode')->ignore($livewire?->cMedicine?->id ?? null),
                        ])
                        ->maxLength(255)
                        ->suffixAction(function () {
                            return Action::make('generateBarcode')
                                ->icon('heroicon-m-sparkles')
                                ->tooltip('Generate Barcode')
                                ->action(function (\Filament\Schemas\Components\Utilities\Set $set) {
                                    $set('barcode', rand(1000000000, 9999999999));
                                });
                        }),
                ])->live(debounce: 500),

                Select::make('manufacturer_id')
                    ->label('Manufacturer')
                    ->options(fn () => Manufacturer::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Group::make()
                            ->schema([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('contact_name')->maxLength(255),
                                TextInput::make('phone')->tel()->maxLength(20),
                                TextInput::make('email')->email()->maxLength(255),
                                TextInput::make('address')->maxLength(255),
                                TextInput::make('website')->url()->maxLength(255),
                                Select::make('country')
                                    ->options(collect(Countries::getNames('en'))->sort()->toArray())
                                    ->searchable()
                                    ->preload(),
                                ToggleButtons::make('is_active')
                                    ->label('Active')
                                    ->boolean()
                                    ->inline()
                                    ->default(true),
                            ])->columns(2)->columnSpanFull(),
                    ])
                    ->createOptionAction(fn (Action $action) => $action->modalHeading('Create manufacturer')->modalWidth('xl'))
                    ->createOptionUsing(function (array $data) {
                        $validator = Validator::make($data, [
                            'name' => ['required', 'string', 'max:255', Rule::unique('manufacturers', 'name')],
                            'contact_name' => ['nullable', 'string', 'max:255'],
                            'phone' => ['nullable', 'string', 'max:20'],
                            'email' => ['nullable', 'email', 'max:255', Rule::unique('manufacturers', 'email')],
                            'address' => ['nullable', 'string', 'max:255'],
                            'website' => ['nullable', 'url', 'max:255'],
                            'country' => ['nullable', 'string', 'max:8'],
                            'is_active' => ['nullable', 'boolean'],
                        ]);
                        $validated = $validator->validate();
                        if (!empty($validated['phone'])) {
                            $validated['phone'] = preg_replace('/\D+/', '', $validated['phone']);
                        }
                        $manufacturer = Manufacturer::create($validated);
                        return $manufacturer->id;
                    }),
            ]),

            Section::make()->columns(['sm' => 2])->schema([
                TextInput::make('potency')
                    ->label('Potency')
                    ->maxLength(50)
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $livewire ? $livewire->computeAndSetSku($get, $set) : null),

                Select::make('form')
                    ->options(Medicine::forms())
                    ->label('Form')
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $livewire ? $livewire->computeAndSetSku($get, $set) : null),

                TextInput::make('packing_quantity')
                    ->label('Packing Quantity')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($get, $set) => $livewire ? $livewire->computeAndSetSku($get, $set) : null),

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
                    ->afterStateUpdated(fn ($get, $set) => $livewire ? $livewire->computeAndSetSku($get, $set) : null),
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
                    ->afterStateUpdated(function($get, $set) {
                        $purchase = (float) ($get('purchase_price') ?? 0);
                        if ($purchase <= 0) return;
                        $margin = (float) ($get('margin') ?? 0);
                        $sale = $purchase * (1 + ($margin / 100));
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
                            ->schema([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('rate')->required()->maxLength(255),
                                ToggleButtons::make('is_active')->boolean()->inline()->default(true),
                            ])->columns(3)->columnSpanFull(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $validator = Validator::make($data, [
                            'name' => ['required', 'string', 'max:255', Rule::unique('taxes', 'name')],
                            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
                            'is_active' => ['nullable', 'boolean'],
                        ]);
                        $validated = $validator->validate();
                        $tax = Tax::create($validated);
                        return $tax->id;
                    })
                    ->createOptionAction(fn (Action $action) => $action->modalHeading('Create tax')->modalWidth('xl')),
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
                    ->afterStateUpdated(function($get, $set) {
                       $purchase = (float) ($get('purchase_price') ?? 0);
                        if ($purchase <= 0) return;
                        $sale = (float) ($get('sale_price') ?? 0);
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
                            Rule::unique('medicines', 'sku')->ignore($livewire?->cMedicine?->id ?? null),
                        ])
                        ->dehydrated()
                        ->maxLength(255),
                    ToggleButtons::make('is_active')
                        ->label('Active?')
                        ->boolean()
                        ->grouped()
                        ->default(true),
                ]),
        ];
    }
}
