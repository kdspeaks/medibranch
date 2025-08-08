<?php

namespace App\Livewire\Pages\Purchase\Concerns;

use Filament\Forms\Components\View;
use App\Models\{Branch, Supplier, Medicine, Tax, InventoryBatch, Purchase};
use Filament\Forms\Components\{Section, Group, Grid, Select, TextInput, DatePicker, Textarea, Repeater, ToggleButtons};

trait HasPurchaseForm
{
    public ?Purchase $cPurchase = null;
    public string $medicineSearch = '';
    public array $medicineSuggestions = [];

    public function setPurchase(Purchase $purchase): void
    {
        $this->cPurchase = $purchase;
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

    public function purchaseFormSchema(): array
    {
        return [
            Section::make('Purchase Details')
                ->columns(3)
                ->schema([
                    Select::make('branch_id')
                        ->label('Branch')
                        ->required()
                        ->searchable()
                        ->relationship('branch', 'name'),

                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->nullable()
                        ->searchable()
                        ->relationship('supplier', 'name'),

                    TextInput::make('invoice_number')
                        ->label('Invoice No.')
                        ->maxLength(255)
                        ->columnSpan(1),

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
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending'),
                ]),

            Section::make('Items')
                ->schema([
                    View::make('livewire.pages.purchase.medicine-search-dropdown')
                        ->label('Search or Scan Medicine'),


                    Repeater::make('')
                        ->label('Purchase Items')
                        ->relationship()
                        ->columns(3)
                        ->schema([
                            Select::make('medicine_id')
                                ->label('Medicine')
                                ->searchable()
                                ->required(),
                            // ->relationship('medicine', 'name'),

                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->required()
                                ->minValue(1),

                            TextInput::make('unit_purchase_price')
                                ->label('Purchase Price')
                                ->numeric()
                                ->required()
                                ->minValue(0),

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
                                ->relationship('tax', 'name'),

                            TextInput::make('total_amount')
                                ->label('Line Total')
                                ->numeric()
                                ->dehydrated()
                                ->disabled()
                                ->default(0.00),
                        ])
                        ->createItemButtonLabel('Add Item')
                        ->collapsible()
                        ->statePath('purchase_items') // Explicitly define path,
                ]),

            Section::make('Additional Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->nullable(),
                ])
        ];
    }





    public function updatedMedicineSearch($value)
    {
        dd($value);
        // Search only if 2+ characters
        if (strlen($value) < 2) {
            $this->medicineSuggestions = [];
            return;
        }

        $this->medicineSuggestions = Medicine::query()
            ->where('name', 'like', '%' . $value . '%')
            ->orWhere('barcode', $value)
            ->limit(10)
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'barcode' => $medicine->barcode,
                    'price' => $medicine->purchase_price,
                ];
            })
            ->toArray();

        // dd($this->medicineSuggestions);
    }

    public function selectMedicineFromSearch($medicineId)
    {
        $medicine = Medicine::find($medicineId);
        if (! $medicine) return;

        $this->addPurchaseItem($medicine);
        $this->medicineSearch = '';
        $this->medicineSuggestions = [];
    }


    public function addPurchaseItem(Medicine $medicine)
    {
        // Check if already added
        if (collect($this->purchase_items)->contains('medicine_id', $medicine->id)) {
            return; // Or increase quantity if desired
        }

        $this->purchase_items[] = [
            'medicine_id' => $medicine->id,
            'quantity' => 1,
            'unit_purchase_price' => $medicine->purchase_price ?? 0,
            'unit_selling_price' => $medicine->selling_price ?? 0,
            'batch_number' => '',
            'mfg_date' => null,
            'expiry_date' => null,
            'tax_id' => null,
            'total_amount' => 0,
        ];
    }
}
