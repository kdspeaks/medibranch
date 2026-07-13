<?php

namespace App\Livewire\Pages\Medicines\Components;

use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Models\PurchaseItem;
use Exception;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MedicineMovementsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

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
                        if (! $record->source) {
                            return 'System';
                        }
                        if ($record->source instanceof PurchaseItem) {
                            $prefix = $record->source->purchase?->ref_code_prefix ?? '';
                            $count = $record->source->purchase?->ref_code_count ?? '';

                            return trim('Purchase #'.$prefix.$count);
                        }

                        return class_basename($record->source);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_transaction'))
                    ->form([
                        DateTimePicker::make('created_at')
                            ->label(__('messages.date'))
                            ->required(),
                        TextInput::make('reason')
                            ->label(__('messages.reason'))
                            ->maxLength(255),
                        TextInput::make('quantity')
                            ->label(__('messages.quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->visible(fn (InventoryLog $record) => $record->source_type === null), // Only manual entries can edit quantity
                        TextInput::make('batch_number')
                            ->label(__('messages.batch_number'))
                            ->maxLength(255)
                            ->visible(fn (InventoryLog $record) => $record->source_type === null && $record->type === 'in'),
                        DatePicker::make('mfg_date')
                            ->label(__('messages.mfg_date'))
                            ->visible(fn (InventoryLog $record) => $record->source_type === null && $record->type === 'in'),
                        DatePicker::make('expiry_date')
                            ->label(__('messages.expiry_date'))
                            ->visible(fn (InventoryLog $record) => $record->source_type === null && $record->type === 'in'),
                    ])
                    ->fillForm(function (InventoryLog $record): array {
                        return [
                            'created_at' => $record->created_at,
                            'reason' => $record->reason,
                            'quantity' => $record->quantity,
                            'batch_number' => $record->batch?->batch_number,
                            'mfg_date' => $record->batch?->mfg_date,
                            'expiry_date' => $record->batch?->expiry_date,
                        ];
                    })
                    ->action(function (array $data, InventoryLog $record) {
                        try {
                            DB::transaction(function () use ($data, $record) {
                                // Update log fields
                                $record->created_at = $data['created_at'];
                                $record->reason = $data['reason'] ?? $record->reason;

                                if ($record->source_type === null) {
                                    $newQty = (int) $data['quantity'];
                                    $oldQty = $record->quantity;
                                    $diff = $newQty - $oldQty;
                                    $batch = $record->batch;

                                    if ($diff !== 0) {
                                        if ($record->type === 'in') {
                                            $batch->quantity += $diff;
                                            $batch->available_quantity += $diff;
                                            if ($batch->available_quantity < 0) {
                                                throw new Exception('Cannot reduce quantity below what has already been consumed.');
                                            }
                                        } elseif ($record->type === 'out') {
                                            // For 'out', a larger quantity means we take more FROM available
                                            $batch->available_quantity -= $diff;
                                            if ($batch->available_quantity < 0) {
                                                throw new Exception('Cannot increase out quantity beyond what is available in the batch.');
                                            }
                                        }
                                        $record->quantity = $newQty;
                                    }

                                    // Update batch fields if type is 'in'
                                    if ($record->type === 'in') {
                                        $batch->batch_number = $data['batch_number'] ?? $batch->batch_number;
                                        $batch->mfg_date = $data['mfg_date'] ?? $batch->mfg_date;
                                        $batch->expiry_date = $data['expiry_date'] ?? $batch->expiry_date;
                                    }

                                    $batch->save();
                                }

                                $record->save();
                            });

                            Notification::make()
                                ->title('Transaction updated successfully.')
                                ->success()
                                ->send();

                        } catch (Exception $e) {
                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (InventoryLog $record) => $record->source_type === null && (auth()->user()?->can('edit-stock-transactions') ?? false)),
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
