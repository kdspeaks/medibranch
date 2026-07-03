<?php

namespace App\Tables\Schemas;

use App\Models\Medicine;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MedicineTableSchema
{
    public static function table(Table $table, $queryBuilder = null): Table
    {
        $branchId = activeBranch()->id ?? null;
        
        $query = $queryBuilder ?? Medicine::query()->with([
            'tax',
            'inventories' => function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            }
        ]);

        return $table
            ->query($query)
            ->columns([
                ViewColumn::make('name')
                    ->view('components.datatable.medicine_name')
                    ->searchable(['name', 'sku'])
                    ->sortable(),
                    
                TextColumn::make('stock_available')
                    ->label(__('messages.stock') ?? 'Stock')
                    ->state(fn ($record) => $record->inventories->first()?->quantity ?? 0)
                    ->badge()
                    ->color(fn ($state) => (int)$state > 0 ? 'success' : 'danger')
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($branchId) {
                        if (!$branchId) return $query;
                        return $query->orderBy(
                            \App\Models\InventoryBatch::selectRaw('COALESCE(SUM(inventory_batches.available_quantity), 0)')
                                ->join('inventories', 'inventories.id', '=', 'inventory_batches.inventory_id')
                                ->whereColumn('inventories.medicine_id', 'medicines.id')
                                ->where('inventories.branch_id', $branchId)
                                ->whereNull('inventories.deleted_at'),
                            $direction
                        );
                    }),

                TextColumn::make('potency')
                    ->separator(', '),
                TextColumn::make('medicineForm.name')
                    ->label(__('messages.form'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('barcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('packing_info')
                    ->label(__('messages.packing'))
                    ->state(fn($record) => "{$record->packing_quantity} {$record->medicineUnit?->name}"),
                TextColumn::make('price_info')
                    ->label(__('messages.last_updated_price'))
                    ->view('components.datatable.medicine_price'),
                TextColumn::make('tax.name')
                    ->separator(', '),
                ToggleColumn::make('is_active')
                    ->label(__('messages.active_question'))
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
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
                    ->url(fn(Medicine $record) => route('medicines.edit', ['medicine' => $record]))
                    ->extraAttributes(['wire:navigate' => true]),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->requiresConfirmation()
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->recordUrl(
                fn(Medicine $record) => route('medicines.view', ['medicine' => $record])
            )
            ->striped();
    }
}
