<?php

namespace App\Livewire\Pages\Medicines\Components;

use App\Models\Medicine;
use App\Models\PurchaseItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Livewire\Component;

class MedicinePurchasesTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public Medicine $medicine;
    #[\Livewire\Attributes\Reactive]
    public ?int $branchId = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseItem::query()
                    ->where('medicine_id', $this->medicine->id)
                    ->with(['purchase.branch', 'purchase.supplier', 'inventoryBatch'])
                    ->when($this->branchId, fn ($query) => $query->whereHas('purchase', fn ($purchase) => $purchase->forBranch($this->branchId)))
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('purchase_ref')
                    ->label(__('messages.purchase'))
                    ->fontFamily('mono')
                    ->size('TextColumn::SIZE_XS')
                    ->state(fn (PurchaseItem $record) => trim(($record->purchase?->ref_code_prefix ?? '') . ($record->purchase?->ref_code_count ?? '')) ?: '-'),
                TextColumn::make('purchase.purchase_date')
                    ->label(__('messages.date'))
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('purchase.branch.name')
                    ->label(__('messages.branch'))
                    ->sortable(),
                TextColumn::make('purchase.supplier.name')
                    ->label(__('messages.supplier'))
                    ->default(__('messages.walk_in'))
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->label(__('messages.batch'))
                    ->fontFamily('mono')
                    ->searchable()
                    ->default('-'),
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_purchase_price')
                    ->label(__('messages.unit_price'))
                    ->money('INR'),
                TextColumn::make('mrp')
                    ->label(__('messages.mrp'))
                    ->money('INR'),
                TextColumn::make('discount_on_purchase')
                    ->label(__('messages.discount_on_purchase'))
                    ->suffix('%')
                    ->numeric(),
                TextColumn::make('line_total_amount')
                    ->label(__('messages.total'))
                    ->money('INR'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'stocked' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->deferLoading();
    }

    public function render()
    {
        return view('livewire.pages.medicines.components.medicine-purchases-table');
    }
}
