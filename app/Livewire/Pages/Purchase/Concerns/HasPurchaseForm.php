<?php

namespace App\Livewire\Pages\Purchase\Concerns;

use Filament\Schemas\Components\Livewire;
use App\Livewire\Pages\Medicines\MedicineSearch;
use Filament\Forms\Components\Hidden;
use App\Models\{Branch, Supplier, Medicine, Tax, Purchase};
use Filament\Forms\Components\{Section, Select, TextInput, DatePicker, Textarea, Repeater};

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
        $validated = $this->form->getState();

        if ($this->cPurchase?->exists) {
            // --- Update existing purchase ---
            $this->cPurchase->update($validated);
            $this->cPurchase->items()->delete();

            foreach ($validated['items'] ?? [] as $item) {
                $this->cPurchase->items()->create($item);
            }

            return $this->cPurchase;
        }

        // --- Create new purchase ---
        $purchase = Purchase::create($validated);

        foreach ($validated['items'] ?? [] as $item) {
            $purchase->items()->create($item);
        }

        return $purchase;
    }

    public function purchaseFormSchema(): array
    {
        return [
            \Filament\Schemas\Components\Section::make('Purchase Details')
                ->columns(3)
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

                    TextInput::make('invoice_number')
                        ->label('Invoice No.')
                        ->maxLength(255),

                    DatePicker::make('purchase_date')
                        ->label('Purchase Date')
                        ->default(now())
                        ->required(),

                    TextInput::make('ref_code_prefix')
                        ->label('Ref Prefix')
                        ->maxLength(50),

                    TextInput::make('ref_code_count')
                        ->label('Ref Count')
                        ->maxLength(50)
                        ->required(),

                    TextInput::make('total_amount')
                        ->label('Total Amount')
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

            \Filament\Schemas\Components\Section::make('Items')
                ->schema([
                    Livewire::make(MedicineSearch::class)
                        ->key('medicine-search') // unique key for this instance
                        ->lazy()
                        ->label('Search or Scan Medicine'),
                    Repeater::make('items')
                        ->addable(false)
                        ->defaultItems(0)
                        ->columns(9)
                        ->schema([
                            Hidden::make('medicine_id'),

                            TextInput::make('medicine_name')
                                ->label('Medicine')
                                ->disabled(),

                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->reactive()
                                ->afterStateUpdated(
                                    fn($state, $set, $get) =>
                                    $set('total_amount', (float)$state * (float)$get('unit_purchase_price'))
                                ),

                            TextInput::make('unit_purchase_price')
                                ->label('Purchase Price')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->reactive()
                                ->afterStateUpdated(
                                    fn($state, $set, $get) =>
                                    $set('total_amount', (float)$get('quantity') * (float)$state)
                                ),

                            TextInput::make('unit_selling_price')
                                ->label('Selling Price')
                                ->numeric()
                                ->required()
                                ->minValue(0),

                            TextInput::make('batch_number')
                                ->label('Batch No.')
                                ->maxLength(255)
                                ->nullable(),

                            DatePicker::make('mfg_date')
                                ->label('Mfg Date')
                                ->nullable(),

                            DatePicker::make('expiry_date')
                                ->label('Expiry Date')
                                ->nullable(),

                            Select::make('tax_id')
                                ->label('Tax')
                                ->nullable()
                                ->searchable()
                                ->options(fn() => Tax::pluck('name', 'id')->toArray()),

                            TextInput::make('total_amount')
                                ->label('Line Total')
                                ->numeric()
                                ->dehydrated()
                                ->disabled()
                                ->default(0.00),
                        ])
                        ->collapsible()
                        ->afterStateUpdated(function ($state, $set) {
                            // $state is the updated repeater array
                            $total = collect($state)
                                ->sum(fn($item) => (float) ($item['total_amount'] ?? 0));

                            $set('total_amount', $total);
                        }),
                ]),

            \Filament\Schemas\Components\Section::make('Additional Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->nullable(),
                ])
        ];
    }
}
