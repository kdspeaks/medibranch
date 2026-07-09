<?php

namespace App\Livewire\Pages\Medicines\Components;

use App\Models\InventoryLog;
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

class MedicineMovementsTable extends Component implements HasForms, HasTable, HasActions
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
                InventoryLog::query()
                    ->whereHas('batch.inventory', function ($query) {
                        $query->where('medicine_id', $this->medicine->id)
                            ->when($this->branchId, fn ($inventory) => $inventory->forBranch($this->branchId));
                    })
                    ->with(['batch.inventory.branch', 'source'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('messages.date'))
                    ->dateTime('d M, Y H:i')
                    ->sortable(),
                TextColumn::make('batch.inventory.branch.name')
                    ->label(__('messages.branch'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'adjustment' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('batch.batch_number')
                    ->label(__('messages.batch'))
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label(__('messages.reason')),
                TextColumn::make('source_label')
                    ->label(__('messages.source'))
                    ->state(function (InventoryLog $record) {
                        if (! $record->source) return 'System';
                        if ($record->source instanceof PurchaseItem) {
                            $prefix = $record->source->purchase?->ref_code_prefix ?? '';
                            $count = $record->source->purchase?->ref_code_count ?? '';
                            return trim('Purchase #' . $prefix . $count);
                        }
                        return class_basename($record->source);
                    }),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->heading(__('messages.stock_transactions'))
            ->description(__('messages.transactions_description'));
    }

    public function render()
    {
        return view('livewire.pages.medicines.components.medicine-movements-table');
    }
}
