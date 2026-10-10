<?php

namespace App\Tables\Schemas;

use App\Models\Medicine;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MedicineTableSchema
{
    public static function table(Table $table, $queryBuilder = null): Table
    {
        $query = $queryBuilder ?? Medicine::query()
            ->select('medicines.*')
            ->selectSub(function ($sub) {
                $sub->from('sale_items')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->whereColumn('sale_items.medicine_id', 'medicines.id')
                    ->select('sales.sale_date')
                    ->latest('sales.sale_date')
                    ->limit(1);
            }, 'last_sold_at')
            ->with([
                'tax',
                'manufacturer',
                'medicineForm',
                'medicineUnit',
                'inventories.branch',
                'inventories.batches',
            ]);

        return $table
            ->query($query)
            ->columns([
                ViewColumn::make('name')
                    ->view('components.datatable.medicine_name')
                    ->searchable(['name', 'sku', 'potency', 'packing_quantity'])
                    ->sortable(),

                ViewColumn::make('stock_available')
                    ->label(__('messages.stock') ?? 'Stock')
                    ->view('components.datatable.medicine_stock')
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction, $livewire) {
                        $branchFilter = $livewire->getTableFilterState('branch_id');
                        $branchId = ! empty($branchFilter['value']) ? $branchFilter['value'] : null;

                        $batchQuery = \App\Models\InventoryBatch::selectRaw('COALESCE(SUM(inventory_batches.available_quantity), 0)')
                            ->join('inventories', 'inventories.id', '=', 'inventory_batches.inventory_id')
                            ->whereColumn('inventories.medicine_id', 'medicines.id')
                            ->whereNull('inventories.deleted_at');

                        if ($branchId) {
                            $batchQuery->where('inventories.branch_id', $branchId);
                        }

                        return $query->orderBy($batchQuery, $direction);
                    }),

                TextColumn::make('last_sold_at')
                    ->label(__('messages.last_sale') ?? 'Last Sale')
                    ->since()
                    ->dateTimeTooltip('d M Y, h:i A')
                    ->placeholder(__('messages.never_sold') ?? 'Never')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction, $livewire) {
                        $branchFilter = $livewire->getTableFilterState('branch_id');
                        $branchId = ! empty($branchFilter['value']) ? $branchFilter['value'] : null;

                        $lastSaleQuery = \App\Models\SaleItem::query()
                            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                            ->whereColumn('sale_items.medicine_id', 'medicines.id')
                            ->select('sales.sale_date')
                            ->latest('sales.sale_date')
                            ->limit(1);

                        if ($branchId) {
                            $lastSaleQuery->where('sales.branch_id', $branchId);
                        }

                        return $query->orderBy($lastSaleQuery, $direction);
                    }),

                TextColumn::make('potency')
                    ->separator(', '),
                ViewColumn::make('manufacturer.name')
                    ->label(__('messages.manufacturer'))
                    ->view('components.datatable.medicine_manufacturer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('medicineForm.name')
                    ->label(__('messages.form'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('barcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('packing_info')
                    ->label(__('messages.packing'))
                    ->state(fn ($record) => "{$record->packing_quantity} {$record->medicineUnit?->name}"),
                TextColumn::make('price_info')
                    ->label(__('messages.last_updated_price'))
                    ->view('components.datatable.medicine_price'),
                TextColumn::make('tax.name')
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label(__('messages.active_question'))
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(Auth::user()?->can('manage-medicines'))
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title(__('messages.medicine_updated'))
                            ->body(__('messages.medicine_updated_body'))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Medicine $record) => route('medicines.edit', ['medicine' => $record]))
                    ->extraAttributes(['wire:navigate' => true]),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->name !== 'Super Admin')
                    ->requiresConfirmation(),
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('branch_id')
                    ->label(__('messages.branch'))
                    ->options(function () {
                        if (Auth::user()?->hasRole('Super Admin')) {
                            return \App\Models\Branch::pluck('name', 'id');
                        }

                        return Auth::user()?->branches()->where('is_active', true)->pluck('branches.name', 'branches.id') ?? [];
                    })
                    ->default(fn () => activeBranch()?->id)
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        $branchId = ! empty($data['value']) ? $data['value'] : null;

                        if ($branchId) {
                            $query->with(['inventories' => function ($q) use ($branchId) {
                                $q->where('branch_id', $branchId)->with(['branch', 'batches']);
                            }]);
                        } else {
                            $query->with(['inventories.branch', 'inventories.batches']);
                        }
                    }),
                \Filament\Tables\Filters\SelectFilter::make('manufacturer_id')
                    ->label(__('messages.manufacturer'))
                    ->relationship('manufacturer', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordUrl(
                fn (Medicine $record) => route('medicines.view', ['medicine' => $record])
            )
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('last_sold_at', 'desc')
            ->striped();
    }
}
