<?php

namespace App\Livewire\Pages\Medicines\Components;

use App\Models\Inventory;
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

class MedicineStocksTable extends Component implements HasForms, HasTable, HasActions
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
                Inventory::query()
                    ->where('medicine_id', $this->medicine->id)
                    ->when($this->branchId, fn ($query) => $query->forBranch($this->branchId))
                    ->with(['branch', 'batches' => fn ($query) => $query->available()])
            )
            ->columns([
                TextColumn::make('branch.name')
                    ->label(__('messages.branch'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('branch.code')
                    ->label(__('messages.code'))
                    ->fontFamily('mono')
                    ->size('TextColumn::SIZE_XS'),
                TextColumn::make('quantity')
                    ->label(__('messages.available'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('batches_count')
                    ->label(__('messages.batches'))
                    ->state(fn (Inventory $record) => $record->batches->count()),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->heading(__('messages.current_stock'))
            ->description(__('messages.stock_description'));
    }

    public function render()
    {
        return view('livewire.pages.medicines.components.medicine-stocks-table');
    }
}
