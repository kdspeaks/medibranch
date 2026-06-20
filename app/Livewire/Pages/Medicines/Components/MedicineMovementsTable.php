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
                    ->label('Date')
                    ->dateTime('d M, Y H:i')
                    ->sortable(),
                TextColumn::make('batch.inventory.branch.name')
                    ->label('Branch')
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
                    ->label('Batch')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason'),
                TextColumn::make('source_label')
                    ->label('Source')
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
            ->heading('Stock Transactions')
            ->description('Inventory movement history for this medicine.');
    }

    public function render()
    {
        return view('livewire.pages.medicines.components.medicine-movements-table');
    }
}
