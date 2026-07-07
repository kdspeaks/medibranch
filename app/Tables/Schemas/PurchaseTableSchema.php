<?php

namespace App\Tables\Schemas;

use App\Models\Purchase;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;

class PurchaseTableSchema
{
    public static function table(Table $table, $queryBuilder = null): Table
    {
        return $table
            ->query($queryBuilder ?? Purchase::query()->with(['branch', 'supplier']))
            ->columns([
                TextColumn::make('reference')
                    ->label(__('messages.reference_no'))
                    ->state(fn (Purchase $record) => trim(($record->ref_code_prefix ?? '') . $record->ref_code_count))
                    ->searchable(['ref_code_prefix', 'ref_code_count'])
                    ->sortable(),
                TextColumn::make('invoice_number')
                    ->label(__('messages.invoice_no'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label(__('messages.branch'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label(__('messages.supplier'))
                    ->placeholder(__('messages.walk_in'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->label(__('messages.date'))
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('messages.total'))
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('total_mrp')
                    ->label(__('messages.total_mrp'))
                    ->money('INR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_discount')
                    ->label(__('messages.total_discount'))
                    ->money('INR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Purchase $record) => route('medicines.purchases.view', ['purchase' => $record]))
                    ->extraAttributes(['wire:navigate' => 'true']),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn (Purchase $record) => $record->status !== 'received')
                    ->url(fn (Purchase $record) => route('medicines.purchases.edit', ['purchase' => $record]))
                    ->extraAttributes(['wire:navigate' => 'true']),
                DeleteAction::make()
                    ->visible(fn (Purchase $record) => $record->status !== 'received')
                    ->requiresConfirmation(),
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->recordUrl(fn (Purchase $record) => route('medicines.purchases.view', ['purchase' => $record]));
    }
}
