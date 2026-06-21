<?php

namespace App\Forms\Schemas;

use App\Models\Tax;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Actions\Action;
use Illuminate\Validation\Rule;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Livewire;
use Illuminate\Support\Facades\Validator;
use Filament\Schemas\Components\FusedGroup;
use App\Livewire\Pages\Medicines\MedicineSearch;

class PurchaseFormSchema
{
    public static function schema($livewire = null): array
    {
        return [
            Section::make(__('messages.purchase_details'))
                ->columns(3)
                ->collapsible()
                ->schema([
                    Select::make('branch_id')
                        ->label(__('messages.branch'))
                        ->required()
                        ->searchable()
                        ->options(fn() => $livewire ? $livewire->branchOptions() : Branch::pluck('name', 'id')->toArray())
                        ->default(fn() => activeBranch()?->id ?? null),

                    Select::make('supplier_id')
                        ->label(__('messages.supplier'))
                        ->nullable()
                        ->searchable()
                        ->options(fn() => Supplier::pluck('name', 'id')->toArray())
                        ->createOptionForm([
                            Group::make()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('name')->required()->maxLength(255),
                                    TextInput::make('contact_person')->maxLength(255),
                                    TextInput::make('email')->email()->maxLength(255),
                                    TextInput::make('phone')->tel()->maxLength(20),
                                ]),
                            Group::make()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('address')->maxLength(255),
                                    TextInput::make('city')->maxLength(255),
                                    TextInput::make('state')->maxLength(255),
                                    TextInput::make('country')->maxLength(255),
                                    TextInput::make('postal_code')->maxLength(20),
                                ]),
                        ])
                        ->createOptionAction(fn (Action $action) => $action->modalHeading('Create supplier'))
                        ->createOptionUsing(function (array $data) {
                            $validator = Validator::make($data, [
                                'name'           => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')],
                                'contact_person' => ['nullable', 'string', 'max:255'],
                                'email'          => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')],
                                'phone'          => ['nullable', 'string', 'max:20', Rule::unique('suppliers', 'phone')],
                                'address'        => ['nullable', 'string', 'max:255'],
                                'city'           => ['nullable', 'string', 'max:255'],
                                'state'          => ['nullable', 'string', 'max:255'],
                                'country'        => ['nullable', 'string', 'max:255'],
                                'postal_code'    => ['nullable', 'string', 'max:20'],
                            ]);
                            $validated = $validator->validate();
                            if (!empty($validated['phone'])) {
                                $validated['phone'] = preg_replace('/\D+/', '', $validated['phone']);
                            }
                            $supplier = Supplier::create($validated);
                            return $supplier->id;
                        }),

                    FusedGroup::make([
                        TextInput::make('ref_code_prefix')
                            ->default("PO/")
                            ->prefixIcon(Heroicon::NumberedList),
                        TextInput::make('ref_code_count')
                            ->prefixIcon(Heroicon::Hashtag)
                            ->default(fn () => (Purchase::max('ref_code_count') ?? 0) + 1),
                    ])->label(__('messages.reference_no'))->columns(2),

                    TextInput::make('invoice_number')
                        ->label(__('messages.invoice_no'))
                        ->maxLength(255)
                        ->prefixIcon(Heroicon::DocumentText),

                    DatePicker::make('purchase_date')
                        ->label(__('messages.purchase_date'))
                        ->default(now())
                        ->displayFormat('d/m/Y')
                        ->native(false)
                        ->required()
                        ->prefixIcon(Heroicon::Calendar),

                    TextInput::make('total_amount')
                        ->label(__('messages.total_amount'))
                        ->prefix('₹')
                        ->readOnly()
                        ->numeric()
                        ->dehydrated()
                        ->default(0.00),

                    Select::make('status')
                        ->label(__('messages.status'))
                        ->required()
                        ->options([
                            'draft' => __('messages.draft'),
                            'received' => __('messages.received'),
                            'cancelled' => __('messages.cancelled'),
                        ])
                        ->default('draft'),
                ]),

            Section::make(__('messages.line_items'))
                ->schema([
                    Livewire::make(MedicineSearch::class)->key('medicine-search'),
                    Repeater::make('items')
                        ->hiddenLabel()
                        ->addable(false)
                        ->defaultItems(0)
                        ->columns(9)
                        ->schema([
                            Hidden::make('medicine_id'),
                            TextInput::make('quantity')
                                ->label(__('messages.quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn($state, $set, $get) => $livewire ? $livewire->setLinePrices($state, $set, $get) : null),

                            TextInput::make('unit_purchase_price')
                                ->prefix('₹')
                                ->label(__('messages.purchase_price'))
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn($state, $set, $get) => $livewire ? $livewire->setLinePrices($state, $set, $get) : null),

                            TextInput::make('margin')
                                ->label(__('messages.margin_percentage'))
                                ->prefix('%')
                                ->numeric()
                                ->required()
                                ->minValue(0),

                            TextInput::make('batch_number')
                                ->label(__('messages.batch_no'))
                                ->maxLength(255)
                                ->nullable(),

                            DatePicker::make('mfg_date')
                                ->label(__('messages.mfg_date'))
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->nullable(),

                            DatePicker::make('expiry_date')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->label(__('messages.expiry_date'))
                                ->nullable(),

                            Select::make('tax_id')
                                ->label(__('messages.tax'))
                                ->nullable()
                                ->options(fn() => Tax::pluck('name', 'id')->toArray())
                                ->native(true)
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn($state, $set, $get) => $livewire ? $livewire->setLinePrices($state, $set, $get) : null),

                            TextInput::make('tax_amount')
                                ->prefix('₹')
                                ->label(__('messages.tax_amount'))
                                ->numeric()
                                ->readOnly()
                                ->default(0.00)
                                ->reactive(),

                            TextInput::make('line_total_amount')
                                ->prefix('₹')
                                ->label(__('messages.total'))
                                ->numeric()
                                ->readOnly()
                                ->default(0.00)
                                ->reactive(),
                        ])
                        ->itemLabel(function ($state): ?string {
                            return collect([
                                $state['medicine_name'] ?? null,
                                isset($state['quantity'], $state['unit_purchase_price'])
                                    ? "Qty: {$state['quantity']}, Purchase Price: {$state['unit_purchase_price']} ,Tax: {$state['tax_amount']}, Total: {$state['line_total_amount']}"
                                    : null
                            ])->filter()->join(' ');
                        })
                        ->collapsible()
                        ->deleteAction(fn(Action $action) => $action->requiresConfirmation())
                        ->afterStateUpdated(function ($state, $set) use ($livewire) {
                            if (!$livewire) return;
                            $totalInCents = collect($state)->reduce(function ($carry, $item) use ($livewire) {
                                $quantity = isset($item['quantity']) ? (float) $item['quantity'] : 0.0;
                                $unit_price = isset($item['unit_purchase_price']) ? (float) $item['unit_purchase_price'] : 0.0;
                                $tax_id = isset($item['tax_id']) ? (int) $item['tax_id'] : 0;
                                if (!is_numeric($quantity) || !is_numeric($unit_price)) return $carry;
                                $line = $livewire->computeLineWithTax((int)$quantity, (float)$unit_price, $tax_id)['line_total_amount'] ?? 0.0;
                                return $carry + (int) round($line * 100);
                            }, 0);
                            $set('total_amount', round($totalInCents / 100, 2));
                        }),
                ]),

            Section::make(__('messages.additional_notes'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('messages.notes'))
                        ->rows(3)
                        ->maxLength(65535)
                ])
        ];
    }
}
