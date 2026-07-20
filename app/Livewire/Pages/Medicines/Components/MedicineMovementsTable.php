<?php

namespace App\Livewire\Pages\Medicines\Components;

use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Models\PurchaseItem;
use Exception;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
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
                TextColumn::make('batch.mrp')
                    ->label(__('messages.mrp') ?? 'MRP')
                    ->numeric()
                    ->prefix(currency())
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
                    ->form(\App\Forms\Schemas\StockAdjustmentFormSchema::schema(
                        medicine: $this->medicine,
                        isEdit: true
                    ))
                    ->fillForm(function (InventoryLog $record): array {
                        return [
                            'created_at' => $record->created_at,
                            'reason' => $record->reason,
                            'quantity' => $record->quantity,
                            'batch_number' => $record->batch?->batch_number,
                            'mfg_date' => $record->batch?->mfg_date,
                            'expiry_date' => $record->batch?->expiry_date,
                            'mrp' => $record->batch?->mrp,
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
                                    $oldBatch = $record->batch;
                                    $inventory = $oldBatch->inventory;

                                    if ($record->type === 'in') {
                                        $newBatchNumber = array_key_exists('batch_number', $data) ? $data['batch_number'] : $oldBatch->batch_number;

                                        if ($oldBatch->batch_number !== $newBatchNumber) {
                                            // Find or create the new batch
                                            $newBatch = $inventory->batches()
                                                ->where('batch_number', $newBatchNumber)
                                                ->first();

                                            if (! $newBatch) {
                                                $newBatch = $inventory->batches()->create([
                                                    'batch_number' => $newBatchNumber,
                                                    'quantity' => 0,
                                                    'available_quantity' => 0,
                                                    'mfg_date' => array_key_exists('mfg_date', $data) ? $data['mfg_date'] : $oldBatch->mfg_date,
                                                    'expiry_date' => array_key_exists('expiry_date', $data) ? $data['expiry_date'] : $oldBatch->expiry_date,
                                                    'unit_purchase_price' => $oldBatch->unit_purchase_price,
                                                    'mrp' => array_key_exists('mrp', $data) ? $data['mrp'] : $oldBatch->mrp,
                                                    'discount_on_purchase' => $oldBatch->discount_on_purchase,
                                                    'status' => 'active',
                                                ]);
                                            } else {
                                                // Update attributes if provided
                                                if (array_key_exists('mfg_date', $data)) {
                                                    $newBatch->mfg_date = $data['mfg_date'];
                                                }
                                                if (array_key_exists('expiry_date', $data)) {
                                                    $newBatch->expiry_date = $data['expiry_date'];
                                                }
                                                if (array_key_exists('mrp', $data)) {
                                                    $newBatch->mrp = $data['mrp'];
                                                }
                                            }

                                            // Move stock out of old batch
                                            $oldBatch->quantity -= $oldQty;
                                            $oldBatch->available_quantity -= $oldQty;
                                            if ($oldBatch->available_quantity < 0) {
                                                throw new Exception("Cannot move stock: old batch '{$oldBatch->batch_number}' does not have enough available quantity.");
                                            }
                                            $oldBatch->save();

                                            // Add stock to new batch
                                            $newBatch->quantity += $newQty;
                                            $newBatch->available_quantity += $newQty;

                                            if (array_key_exists('mrp', $data) && $data['mrp'] !== null) {
                                                $inventory->medicine->update(['mrp' => $data['mrp']]);
                                            }
                                            $newBatch->save();

                                            // Point log to new batch
                                            $record->inventory_batch_id = $newBatch->id;
                                            $record->quantity = $newQty;

                                        } else {
                                            // Batch number hasn't changed, update existing batch
                                            $diff = $newQty - $oldQty;
                                            if ($diff !== 0) {
                                                $oldBatch->quantity += $diff;
                                                $oldBatch->available_quantity += $diff;
                                                if ($oldBatch->available_quantity < 0) {
                                                    throw new Exception('Cannot reduce quantity below what has already been consumed.');
                                                }
                                                $record->quantity = $newQty;
                                            }

                                            if (array_key_exists('mfg_date', $data)) {
                                                $oldBatch->mfg_date = $data['mfg_date'];
                                            }
                                            if (array_key_exists('expiry_date', $data)) {
                                                $oldBatch->expiry_date = $data['expiry_date'];
                                            }

                                            if (array_key_exists('mrp', $data)) {
                                                $oldBatch->mrp = $data['mrp'];
                                                if ($data['mrp'] !== null) {
                                                    $inventory->medicine->update(['mrp' => $data['mrp']]);
                                                }
                                            }
                                            $oldBatch->save();
                                        }
                                    } elseif ($record->type === 'out') {
                                        $diff = $newQty - $oldQty;
                                        if ($diff !== 0) {
                                            $oldBatch->available_quantity -= $diff;
                                            if ($oldBatch->available_quantity < 0) {
                                                throw new Exception('Cannot increase out quantity beyond what is available in the batch.');
                                            }
                                            $record->quantity = $newQty;
                                            $oldBatch->save();
                                        }
                                    }
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
            ->deferLoading();
    }

    public function render()
    {
        return view('livewire.pages.medicines.components.medicine-movements-table');
    }
}
