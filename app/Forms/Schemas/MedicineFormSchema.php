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
        $afterStateUpdatedSku = function ($get, $set) use ($livewire) {
            if ($livewire && $livewire->cMedicine && $livewire->cMedicine->exists) {
                return; // Lock SKU regeneration on edit
            }
            if ($livewire) {
                $livewire->computeAndSetSku($get, $set);
            }
        };

        return [
            Section::make()->columns(['sm' => 3])->schema([
                TextInput::make('name')
                    ->label(__('messages.medicine_name'))
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated($afterStateUpdatedSku),

                Group::make([
                    TextInput::make('barcode')
                        ->label(__('messages.barcode'))
                        ->required()
                        ->rules([
                            Rule::unique('medicines', 'barcode')->ignore($livewire?->cMedicine?->id ?? null),
                        ])
                        ->maxLength(255)
                        ->suffixAction(function () {
                            return Action::make('generateBarcode')
                                ->icon('heroicon-m-sparkles')
                                ->tooltip(__('messages.generate_barcode'))
                                ->action(function (\Filament\Schemas\Components\Utilities\Set $set) {
                                    $set('barcode', rand(1000000000, 9999999999));
                                });
                        }),
                ])->live(debounce: 500),

                Select::make('manufacturer_id')
                    ->label(__('messages.manufacturer'))
                    ->options(fn () => Manufacturer::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated($afterStateUpdatedSku)
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
                    ->label(__('messages.potency'))
                    ->maxLength(50)
                    ->live(debounce: 500)
                    ->afterStateUpdated($afterStateUpdatedSku),

                Select::make('medicine_form_id')
                    ->options(\App\Models\MedicineForm::where('is_active', true)->pluck('name', 'id'))
                    ->label(__('messages.form'))
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated($afterStateUpdatedSku),

                TextInput::make('packing_quantity')
                    ->label(__('messages.packing_quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated($afterStateUpdatedSku),

                Select::make('medicine_unit_id')
                    ->label(__('messages.unit'))
                    ->required()
                    ->options(function ($get) {
                        $formId = $get('medicine_form_id');
                        if (!$formId) return [];
                        $form = \App\Models\MedicineForm::find($formId);
                        return $form ? $form->units()->where('medicine_units.is_active', true)->pluck('medicine_units.name', 'medicine_units.id') : [];
                    })
                    ->native(false)
                    ->searchable()
                    ->live(debounce: 500)
                    ->afterStateUpdated($afterStateUpdatedSku),
            ]),

            Section::make()->columns(['sm' => 4])->schema([
                TextInput::make('mrp')
                    ->label(__('messages.mrp'))
                    ->numeric()
                    ->default(0.00)
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function($get, $set) {
                        $mrp = (float) ($get('mrp') ?? 0);
                        $discount = (float) ($get('discount_on_purchase') ?? 0);
                        $purchase = $mrp - ($mrp * ($discount / 100));
                        $set('purchase_price', round($purchase, 2));
                    }),
                TextInput::make('discount_on_purchase')
                    ->label(__('messages.discount_on_purchase'))
                    ->numeric()
                    ->default(0.00)
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function($get, $set) {
                        $mrp = (float) ($get('mrp') ?? 0);
                        $discount = (float) ($get('discount_on_purchase') ?? 0);
                        $purchase = $mrp - ($mrp * ($discount / 100));
                        $set('purchase_price', round($purchase, 2));
                    }),
                TextInput::make('purchase_price')
                    ->label(__('messages.purchase_price'))
                    ->numeric()
                    ->default(0.00)
                    ->readOnly()
                    ->required(),
                Select::make('tax_id')
                    ->label(__('messages.tax'))
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
                    ->label(__('messages.tax_included'))
                    ->boolean()
                    ->grouped()
                    ->default(true)
                    ->columns(2),

                TextInput::make('discount_on_sale')
                    ->label(__('messages.discount_on_sale'))
                    ->numeric()
                    ->default(0.00)
                    ->required(),
            ]),

            Section::make()->schema([
                RichEditor::make('description')
                    ->label(__('messages.description'))
                    ->nullable()
                    ->columnSpanFull()
                    ->maxLength(5000),
            ]),
            Section::make()
                ->columns(['sm' => 3])
                ->schema([
                    TextInput::make('sku')
                        ->label(__('messages.sku'))
                        ->disabled()
                        ->rules([
                            Rule::unique('medicines', 'sku')->ignore($livewire?->cMedicine?->id ?? null),
                        ])
                        ->dehydrated()
                        ->maxLength(255),
                    ToggleButtons::make('is_active')
                        ->label(__('messages.active_question'))
                        ->boolean()
                        ->grouped()
                        ->default(true),
                ]),
        ];
    }
}
