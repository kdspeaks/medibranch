<?php

namespace App\Livewire\Pages\Sales\Concerns;

use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

trait HasSaleTable
{
    public function getSaleTableColumns(): array
    {
        return [
            TextColumn::make('invoice_number')->label(__('messages.invoice_no') ?? 'Invoice No')->searchable()->sortable(),
            TextColumn::make('sale_date')->label(__('messages.date') ?? 'Date')->dateTime()->sortable(),
            TextColumn::make('branch.name')->label(__('messages.branch') ?? 'Branch')->sortable()->toggleable(),
            TextColumn::make('customer.name')->label(__('messages.customers') ?? 'Customer')->searchable()->sortable()
                ->formatStateUsing(fn ($state) => $state ?? __('messages.walk_in') ?? 'Walk-in Customer'),
            TextColumn::make('total_amount')->label(__('messages.total_amount') ?? 'Total')->formatStateUsing(fn ($state) => currency().number_format((float) $state, 2))->sortable(),
            TextColumn::make('payment_method')->label(__('messages.payment_method') ?? 'Payment Method')->badge()
                ->color(fn (string $state): string => match ($state) {
                    'cash' => 'success',
                    'card' => 'info',
                    'upi' => 'warning',
                    default => 'gray',
                })->formatStateUsing(fn ($state) => strtoupper($state)),
            TextColumn::make('payment_status')->label(__('messages.payment_status') ?? 'Payment Status')->badge()
                ->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'partial' => 'warning',
                    'unpaid' => 'danger',
                    default => 'gray',
                }),
        ];
    }

    public function getSaleTableFilters(): array
    {
        $user = auth()->user();

        return [
            SelectFilter::make('branch_id')
                ->label(__('messages.branch') ?? 'Branch')
                ->relationship(
                    'branch',
                    'name',
                    fn ($query) => $user?->hasRole('Super Admin')
                        ? $query
                        : $query->whereIn('id', $user?->branches->pluck('id') ?? [])
                ),
            SelectFilter::make('payment_method')
                ->label(__('messages.payment_method') ?? 'Payment Method')
                ->options([
                    'cash' => __('messages.cash') ?? 'Cash',
                    'card' => __('messages.card') ?? 'Card',
                    'upi' => __('messages.upi') ?? 'UPI',
                ]),
        ];
    }

    public function getSaleTableActions(): array
    {
        return [
            Action::make('view')
                ->label(__('messages.view') ?? 'View')
                ->icon('heroicon-o-eye')
                ->url(fn (Sale $record): string => route('sales.view', $record)),
        ];
    }
}
