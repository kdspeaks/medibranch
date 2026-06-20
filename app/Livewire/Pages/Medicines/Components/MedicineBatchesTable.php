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
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('available_quantity')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('quantity')
                    ->label('Total')
                    ->numeric(),
                TextColumn::make('unit_purchase_price')
                    ->label('Purchase')
                    ->money('INR'),
                TextColumn::make('margin')
                    ->label('Margin')
                    ->suffix('%')
                    ->numeric(),
                TextColumn::make('mfg_date')
                    ->label('MFG')
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry')
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
