<?php

namespace App\Livewire\Pages\Purchase\Concerns;

use App\Models\Tax;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PricingService;
use App\Services\PurchaseService;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Filament\Actions\CreateAction;
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
use Filament\Infolists\Components\TextEntry;
use App\Livewire\Pages\Medicines\MedicineSearch;



trait HasPurchaseForm
{
    public ?Purchase $cPurchase = null;

    public function setPurchase(Purchase $purchase): void
    {
        $this->cPurchase = $purchase;
        $this->form->fill($purchase->load('items')->toArray()); // fill with existing data
    }

    public function savePurchase(): Purchase
    {
        $this->cPurchase = app(PurchaseService::class)->save(
            $this->form->getState(),
            $this->cPurchase?->exists ? $this->cPurchase : null
        );

        return $this->cPurchase;
    }


    // helper to compute line totals (returns array: [line_total_float, tax_amount_float, tax_rate_float])
    public function computeLineWithTax(int $qty, float $unitPrice, ?int $taxId)
    {
        return app(PricingService::class)->lineWithTax($qty, $unitPrice, $taxId);
    }

    public function setLinePrices($state, $set, $get)
    {
        // dd($get);
        // Read values from the same repeater item (or top-level form) via $get
        $quantity = is_numeric($get('quantity')) ? (float) $get('quantity') : 0.0;
        $unitPurchase = is_numeric($get('unit_purchase_price')) ? (float) $get('unit_purchase_price') : 0.0;
        $taxId = is_numeric($get('tax_id')) ? (int) $get('tax_id') : 0;

        // If nothing meaningful to compute, set zeros and return early
        if ($quantity <= 0 || $unitPurchase <= 0) {
            $set('line_total_amount', 0.00);
            $set('tax_amount', 0.00);
            return;
        }

        // computeLineWithTax should accept (quantity, unit_price, tax_id)
        $price = $this->computeLineWithTax($quantity, $unitPurchase, $taxId);

        $set('line_total_amount', (float) ($price['line_total_amount'] ?? 0.0));
        $set('tax_amount', (float) ($price['tax_amount'] ?? 0.0));
    }



    public function purchaseFormSchema(Schema $schema)
    {
        return
            $schema->components([

                //Purchase Details Section
                Section::make('Purchase Details')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->required()
                            ->searchable()
                            ->options(fn() => $this->branchOptions())
                            ->default(fn() => activeBranch()?->id ?? null),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->nullable()
                            ->searchable()
                            ->options(fn() => Supplier::pluck('name', 'id')->toArray())
                            ->createOptionForm([
                                Group::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Supplier Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Enter supplier name'),

                                        TextInput::make('contact_person')
                                            ->label('Contact Person')
                                            ->maxLength(255)
                                            ->placeholder('Enter contact person name'),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255)
                                            ->placeholder('Enter email address'),

                                        TextInput::make('phone')
                                            ->label('Phone')
                                            ->tel()
                                            ->maxLength(20)
                                            ->placeholder('Enter phone number'),
                                    ]),

                                Group::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('address')
                                            ->label('Address')
                                            ->maxLength(255)
                                            ->placeholder('Street address'),

                                        TextInput::make('city')
                                            ->label('City')
                                            ->maxLength(255),

                                        TextInput::make('state')
                                            ->label('State')
                                            ->maxLength(255),

                                        TextInput::make('country')
                                            ->label('Country')
                                            ->maxLength(255),

                                        TextInput::make('postal_code')
                                            ->label('Postal Code')
                                            ->maxLength(20),
                                    ]),
                            ])
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->modalHeading('Create supplier')
                                    ->modalSubmitActionLabel('Create supplier');
                            })
                            ->createOptionUsing(function (array $data) {
                                // server-side validation rules aligned to the form fields
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

                                // This will throw a ValidationException on failure and Filament will display errors in the modal
                                $validated = $validator->validate();

                                // Normalize phone (optional) — remove non-digits
                                if (! empty($validated['phone'])) {
                                    $validated['phone'] = preg_replace('/\D+/', '', $validated['phone']);
                                }

                                // Create (or use firstOrCreate if you prefer to avoid duplicates)
                                $supplier = Supplier::create([
                                    'name'           => $validated['name'],
                                    'contact_person' => $validated['contact_person'] ?? null,
                                    'email'          => $validated['email'] ?? null,
                                    'phone'          => $validated['phone'] ?? null,
                                    'address'        => $validated['address'] ?? null,
                                    'city'           => $validated['city'] ?? null,
                                    'state'          => $validated['state'] ?? null,
                                    'country'        => $validated['country'] ?? null,
                                    'postal_code'    => $validated['postal_code'] ?? null,
                                ]);

                                return $supplier->id;
                            }),

                        FusedGroup::make([
                            TextInput::make('ref_code_prefix')
                                ->default("PO/")
                                ->placeholder('Prefix')
                                ->prefixIcon(Heroicon::NumberedList),
                            TextInput::make('ref_code_count')
                                ->prefixIcon(Heroicon::Hashtag)
                                ->placeholder('#')
                                ->default(function () {
                                    $last = Purchase::max('ref_code_count'); // get highest number
                                    return $last ? $last + 1 : 1;            // if null, start at 1
                                }),
                        ])
                            ->label('Reference No')
                            ->columns(2),

                        TextInput::make('invoice_number')
                            ->label('Invoice No.')
                            ->placeholder("ABCD1234")
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::DocumentText),

                        DatePicker::make('purchase_date')
                            ->label('Purchase Date')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),


                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->prefix('₹')
                            ->readOnly()
                            ->numeric()
                            ->dehydrated()
                            ->default(0.00),


                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft'),
                    ]),


                //Medicine List Section
                Section::make('Line Items')
                    // ->collapsible(!$this->cPurchase?->exists ?? false) // if editing existing, start collapsed
                    // ->collapsed()
                    ->schema([
                        Livewire::make(MedicineSearch::class)
                            ->key('medicine-search') // unique key for this instance
                        // ->lazy(),
                        ,
                        // ->label('Search or Scan Medicine'),
                        Repeater::make('items')

                            ->hiddenLabel()
                            ->addable(false)
                            ->defaultItems(0)
                            ->columns(9)
                            ->schema([
                                Hidden::make('medicine_id'),

                                // TextEntry::make('medicine_name')
                                // ->label('Medicine')
                                // ->disabled(),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->live(debounce: 500) // 500ms debounce before Livewire call
                                    ->afterStateUpdated(fn($state, $set, $get) => $this->setLinePrices($state, $set, $get)),

                                TextInput::make('unit_purchase_price')
                                    ->prefix('₹')
                                    ->label('Purchase Price')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->live(debounce: 500) // 500ms debounce before Livewire call
                                    ->afterStateUpdated(fn($state, $set, $get) => $this->setLinePrices($state, $set, $get)),

                                TextInput::make('margin')
                                    ->label('Margin')
                                    ->prefix('%')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),

                                TextInput::make('batch_number')
                                    ->label('Batch No.')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->placeholder('ABCXYZ12345'),

                                DatePicker::make('mfg_date')
                                    ->label('Mfg Date')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->nullable()
                                    ->placeholder('DD/MM/YYYY'),
                                // ->prefixIcon(Heroicon::Calendar),

                                DatePicker::make('expiry_date')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->label('Expiry Date')
                                    ->nullable()
                                    ->placeholder('DD/MM/YYYY'),

                                Select::make('tax_id')
                                    ->label('Tax')
                                    ->nullable()
                                    ->options(fn() => Tax::pluck('name', 'id')->toArray())
                                    ->native(true)
                                    ->live(debounce: 500) // 500ms debounce before Livewire call
                                    ->afterStateUpdated(fn($state, $set, $get) => $this->setLinePrices($state, $set, $get)),

                                // TextEntry::make('tax_amount')
                                //     ->prefix('₹ ')
                                //     ->label('Tax Amount')
                                //     ->numeric()
                                //     ->disabled()
                                //     ->default(0.00)
                                //     ->reactive(),
                                TextInput::make('tax_amount')
                                    ->prefix('₹')
                                    ->label('Tax Amount')
                                    ->numeric()
                                    ->readOnly()
                                    ->default(0.00)
                                    ->reactive(),
                                TextInput::make('line_total_amount')
                                    ->prefix('₹')
                                    ->label('Total')
                                    ->numeric()
                                    ->readOnly()
                                    ->default(0.00)
                                    ->reactive(),

                            ])
                            ->itemLabel(
                                function ($state, $set, $get): ?string {
                                    return collect([
                                        $state['medicine_name'] ?? null,
                                        isset($state['quantity'], $state['unit_purchase_price'])
                                            ? "Qty: {$state['quantity']}, Purchase Price: {$state['unit_purchase_price']} ,Tax: {$state['tax_amount']}, Total: {$state['line_total_amount']}"
                                            : null
                                    ])
                                        ->filter()
                                        ->join(' ');
                                    // return "1";
                                }
                            )
                            ->collapsible()
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->afterStateUpdated(function ($state, $set) {
                                // Sum using integer cents/paise to avoid float imprecision
                                $totalInCents = collect($state)->reduce(function ($carry, $item) {
                                    $quantity = isset($item['quantity']) ? (float) $item['quantity'] : 0.0;
                                    $unit_purchase_price = isset($item['unit_purchase_price']) ? (float) $item['unit_purchase_price'] : 0.0;
                                    $tax_id = isset($item['tax_id']) ? (float) $item['tax_id'] : 0.0;

                                    // If either is non-numeric, treat as 0
                                    if (! is_numeric($quantity) || ! is_numeric($unit_purchase_price)) {
                                        return $carry;
                                    }

                                    $line_total_amount = $this->computeLineWithTax((int)$quantity, (float)$unit_purchase_price, (int)$tax_id)['line_total_amount'] ?? 0.0;

                                    // Multiply qty * price, convert to cents and round to nearest cent
                                    return $carry + (int) round($line_total_amount * 100);
                                }, 0);

                                // Convert back to decimal with two places
                                $total = round($totalInCents / 100, 2);
                                // dd($total);
                                // Set numeric value (store numeric so validation/db work fine)
                                $set('total_amount', $total);
                            }),


                    ]),


                //Additional Details Section
                Section::make('Additional Notes')
                    ->schema([Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->maxLength(65535)
                        ->placeholder('Any additional notes or comments...'),])


            ]);
    }

    private function branchOptions(): array
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole('Super Admin')) {
            return $user->branches()->where('is_active', true)->pluck('name', 'branches.id')->toArray();
        }

        return Branch::pluck('name', 'id')->toArray();
    }
}
