<?php

namespace App\Livewire\Pages\Medicines\Components;

use App\Models\Medicine;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Livewire\Component;

class MedicineBatchesTable extends Component implements HasForms, HasTable, HasActions
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
                \App\Models\InventoryBatch::query()
                    ->whereHas('inventory', fn ($q) => $q->where('medicine_id', $this->medicine->id))
                    ->with('inventory.branch')
                    ->when($this->branchId, fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->forBranch($this->branchId)))
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('inventory.branch.name')
                    ->label(__('messages.branch'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('batch_number')
                    ->label(__('messages.batch'))
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('available_quantity')
                    ->label(__('messages.available'))
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('quantity')
                    ->label(__('messages.total'))
                    ->numeric(),
                TextColumn::make('unit_purchase_price')
                    ->label(__('messages.purchase'))
                    ->money('INR'),
                TextColumn::make('mrp')
                    ->label(__('messages.mrp'))
                    ->money('INR'),
                TextColumn::make('discount_on_purchase')
                    ->label(__('messages.discount_on_purchase'))
                    ->suffix('%')
                    ->numeric(),
                TextColumn::make('mfg_date')
                    ->label(__('messages.mfg'))
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label(__('messages.expiry'))
                    ->date('d M, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expiry_date?->between(now(), now()->addDays(90)) ? 'warning' : null)
                    ->weight(fn ($record) => $record->expiry_date?->between(now(), now()->addDays(90)) ? 'bold' : null),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }

    public function render()
    {
        return view('livewire.pages.medicines.components.medicine-batches-table');
    }
}
