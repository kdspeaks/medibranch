<?php

namespace App\Forms\Schemas;

use App\Models\InventoryLog;
use App\Models\Medicine;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class StockAdjustmentFormSchema
{
    public static function schema(Medicine $medicine, bool $isEdit = false, ?int $scopedBranchId = null, bool $isSuperAdmin = false): array
    {
        $fields = [];

        if ($isEdit) {
            $fields[] = DateTimePicker::make('created_at')
                ->label(__('messages.date'))
                ->required();
        } else {
            $fields[] = Radio::make('adjustment_type')
                ->label(__('messages.adjustment_type'))
                ->options([
                    'in' => __('messages.stock_in'),
                    'out' => __('messages.stock_out'),
                ])
                ->default('in')
                ->inline()
                ->live();

            $fields[] = Select::make('branch_id')
                ->label(__('messages.branch'))
                ->options(function () use ($isSuperAdmin) {
                    if ($isSuperAdmin) {
                        return \App\Models\Branch::pluck('name', 'id');
                    }

                    return auth()->user()?->branches()->where('is_active', true)->pluck('branches.name', 'branches.id') ?? [];
                })
                ->required()
                ->visible(function () use ($isSuperAdmin) {
                    if ($isSuperAdmin) {
                        return true;
                    }

                    return auth()->user()?->branches()->where('is_active', true)->count() > 1;
                })
                ->default($scopedBranchId)
                ->live();
        }

        $fields[] = TextInput::make('quantity')
            ->label(__('messages.quantity'))
            ->numeric()
            ->required()
            ->minValue(1)
            ->visible(fn ($record = null) => $isEdit ? ($record ? $record->source_type === null : false) : true);

        if (! $isEdit) {
            $fields[] = TextInput::make('mrp')
                ->label(__('messages.mrp'))
                ->id('mrp_input')
                ->numeric()
                ->default($medicine->mrp)
                ->required()
                ->extraInputAttributes([
                    'x-on:input' => '
                        let mrp = parseFloat($el.value) || 0;
                        let discount = parseFloat(document.getElementById(\'discount_input\')?.value || 0);
                        let pp = document.getElementById(\'purchase_price_input\');
                        if (pp) {
                            pp.value = (mrp - (mrp * discount / 100)).toFixed(2);
                            pp.dispatchEvent(new Event(\'input\', { bubbles: true }));
                        }
                    ',
                ])
                ->visible(fn ($get) => $get('adjustment_type') === 'in');

            $fields[] = TextInput::make('discount_on_purchase')
                ->label(__('messages.discount_on_purchase'))
                ->id('discount_input')
                ->numeric()
                ->default($medicine->discount_on_purchase ?? 0)
                ->required()
                ->extraInputAttributes([
                    'x-on:input' => '
                        let discount = parseFloat($el.value) || 0;
                        let mrp = parseFloat(document.getElementById(\'mrp_input\')?.value || 0);
                        let pp = document.getElementById(\'purchase_price_input\');
                        if (pp) {
                            pp.value = (mrp - (mrp * discount / 100)).toFixed(2);
                            pp.dispatchEvent(new Event(\'input\', { bubbles: true }));
                        }
                    ',
                ])
                ->visible(fn ($get) => $get('adjustment_type') === 'in');

            $fields[] = TextInput::make('purchase_price')
                ->label(__('messages.purchase_price'))
                ->id('purchase_price_input')
                ->numeric()
                ->default($medicine->purchase_price)
                ->required()
                ->readOnly()
                ->visible(fn ($get) => $get('adjustment_type') === 'in');
        } else {
            $fields[] = TextInput::make('mrp')
                ->label(__('messages.mrp') ?? 'MRP')
                ->numeric()
                ->visible(fn (InventoryLog $record) => $record->source_type === null && $record->type === 'in');
        }

        $fields[] = TextInput::make('batch_number')
            ->label(__('messages.batch_number'))
            ->maxLength(255)
            ->visible(fn ($get, $record = null) => $isEdit
                ? ($record ? $record->source_type === null && $record->type === 'in' : false)
                : $get('adjustment_type') === 'in');

        $fields[] = DatePicker::make('mfg_date')
            ->label(__('messages.mfg_date'))
            ->visible(fn ($get, $record = null) => $isEdit
                ? ($record ? $record->source_type === null && $record->type === 'in' : false)
                : $get('adjustment_type') === 'in');

        $fields[] = DatePicker::make('expiry_date')
            ->label(__('messages.expiry_date'))
            ->visible(fn ($get, $record = null) => $isEdit
                ? ($record ? $record->source_type === null && $record->type === 'in' : false)
                : $get('adjustment_type') === 'in');

        if (! $isEdit) {
            $fields[] = Select::make('preferred_batch_id')
                ->label(__('messages.preferred_batch'))
                ->options(function ($get) use ($scopedBranchId, $medicine) {
                    $branchId = $get('branch_id') ?? $scopedBranchId;
                    if (! $branchId) {
                        return [];
                    }

                    return \App\Models\InventoryBatch::query()
                        ->whereHas('inventory', fn ($q) => $q->where('medicine_id', $medicine->id)->where('branch_id', $branchId))
                        ->available()
                        ->get()
                        ->mapWithKeys(fn ($b) => [$b->id => $b->batch_number ? "{$b->batch_number} ({$b->available_quantity} available)" : "No Batch ({$b->available_quantity} available)"]);
                })
                ->visible(fn ($get) => $get('adjustment_type') === 'out');
        }

        $fields[] = TextInput::make('reason')
            ->label(__('messages.reason'))
            ->required($isEdit ? false : true)
            ->maxLength(255)
            ->default(fn ($get) => $isEdit ? null : ($get('adjustment_type') === 'in' ? 'Manual Stock Entry' : 'Manual Stock Deduction'));

        return $fields;
    }
}
