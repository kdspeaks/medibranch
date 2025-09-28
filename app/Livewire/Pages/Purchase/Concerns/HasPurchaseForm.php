<?php

namespace App\Livewire\Pages\Purchase\Concerns;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\FusedGroup;
use Filament\Infolists\Components\TextEntry;
use App\Livewire\Pages\Medicines\MedicineSearch;
use App\Models\{Branch, Supplier, Medicine, Tax, Purchase};
// use Filament\Forms\Components\{Section, Select, TextInput, DatePicker, Textarea, Repeater};

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
        // dd($this->form->getState());
        $validated = $this->form->getState();

        if ($this->cPurchase?->exists) {
            // --- Update existing purchase ---
            $this->cPurchase->update($validated);
            $this->cPurchase->items()->delete();

            foreach ($validated['items'] ?? [] as $item) {
                $this->cPurchase->items()->create($item);
            }

            dd($this->cPurchase);
            return $this->cPurchase;
        }

        // --- Create new purchase ---
        $purchase = Purchase::create($validated);

        foreach ($validated['items'] ?? [] as $item) {
            $purchase->items()->create($item);
        }

        return $purchase;
    }

    // helper to compute line totals (returns array: [line_total_float, tax_amount_float, tax_rate_float])
    public function computeLineWithTax(int $qty, float $unitPrice, ?int $taxId)
    {
        $taxRate = 0.0;

        if ($taxId) {
            $tax = \App\Models\Tax::find($taxId);
            if ($tax && $tax->is_active) {
                $taxRate = (float) $tax->rate;
            }
        }

        $line = $qty * $unitPrice;
        $taxAmount = ($taxRate > 0) ? ($line * ($taxRate / 100.0)) : 0.0;
        $lineWithTax = $line + $taxAmount;

        // round to 2 decimals for storage (we'll also use paise integer arithmetic for sums)
        return [
            'line_total_amount' => round($lineWithTax, 2),
            'tax_amount' => round($taxAmount, 2),
            'tax_rate'   => round($taxRate, 2),
        ];
    }

    public function setLinePrices($state, $set, $get)
    {
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
                \Filament\Schemas\Components\Section::make('Purchase Details')
                    ->columns(3)
                    ->collapsible()

                    ->schema([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->required()
                            ->searchable()
                            ->options(fn() => Branch::pluck('name', 'id')->toArray())
                            ->default(fn() => activeBranch()?->id ?? null),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->nullable()
                            ->searchable()
                            ->options(fn() => Supplier::pluck('name', 'id')->toArray()),

                        FusedGroup::make([
                            TextInput::make('ref_code_prefix')
                                ->default("PO/")
                                ->placeholder('Prefix'),
                            TextInput::make('ref_code_count')
                                ->prefix("#")
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
                            ->maxLength(255),

                        DatePicker::make('purchase_date')
                            ->label('Purchase Date')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        // TextInput::make('ref_code_prefix')
                        //     ->label('Ref Prefix')
                        //     ->maxLength(50),

                        // TextInput::make('ref_code_count')
                        //     ->label('Ref Count')
                        //     ->maxLength(50)
                        //     ->required(),



                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->prefix('₹')
                            ->disabled()
                            ->numeric()
                            ->dehydrated()
                            ->default(0.00),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending'   => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending'),
                    ]),

                \Filament\Schemas\Components\Section::make('Line Items')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Livewire::make(MedicineSearch::class)
                            ->key('medicine-search') // unique key for this instance
                        // ->lazy(),
                        ,
                        // ->label('Search or Scan Medicine'),
                        Repeater::make('items')
                            ->itemLabel(
                                fn(array $state): ?string =>
                                collect([
                                    $state['medicine_name'] ?? null,
                                    isset($state['quantity'], $state['unit_purchase_price'])
                                        ? $state['quantity'] . ' × ' . $state['unit_purchase_price']
                                        : null,
                                    isset($state['line_total_amount'])
                                        ? '= ' . $state['line_total_amount']
                                        : null,
                                ])
                                    ->filter()
                                    ->join(' ')
                            )
                            ->hiddenLabel()
                            ->addable(false)
                            ->defaultItems(0)
                            ->columns(9)
                            ->schema([
                                Hidden::make('medicine_id'),

                                // TextEntry::make('medicine_name')
                                //     ->label('Medicine')
                                //     ->disabled(),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->live(debounce: 500) // 500ms debounce before Livewire call
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, $set, $get) => $this->setLinePrices($state, $set, $get)),

                                TextInput::make('unit_purchase_price')
                                    ->prefix('₹')
                                    ->label('Purchase Price')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->reactive()
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
                                    ->nullable(),

                                DatePicker::make('mfg_date')
                                    ->label('Mfg Date')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->nullable()
                                    ->prefixIcon(Heroicon::Calendar),

                                DatePicker::make('expiry_date')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->label('Expiry Date')
                                    ->nullable()
                                    ->prefixIcon(Heroicon::Calendar),

                                Select::make('tax_id')
                                    ->label('Tax')
                                    ->nullable()
                                    ->options(fn() => Tax::pluck('name', 'id')->toArray())
                                    ->native(true)
                                    ->live()
                                    ->afterStateUpdated(fn($state, $set, $get) => $this->setLinePrices($state, $set, $get)),

                                TextInput::make('tax_amount')
                                    ->prefix('₹')
                                    ->label('Tax Amount')
                                    ->numeric()
                                    ->disabled()
                                    ->default(0.00)
                                    ->reactive(),
                                TextInput::make('line_total_amount')
                                    ->prefix('₹')
                                    ->label('Total')
                                    ->numeric()
                                    ->disabled()
                                    ->default(0.00)
                                    ->reactive(),

                            ])
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

                \Filament\Schemas\Components\Section::make('Additional Notes')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable(),
                    ])
            ]);
    }
}
