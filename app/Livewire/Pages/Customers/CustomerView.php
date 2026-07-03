<?php

namespace App\Livewire\Pages\Customers;

use App\Models\Customer;
use App\Models\Sale;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;

#[Layout('layouts.app')]
#[Title('Customer Profile')]
class CustomerView extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::where('customer_id', $this->customer->id)->latest('id'))
            ->columns([
                TextColumn::make('invoice_number')->label(__('messages.invoice_no') ?? 'Invoice No')->searchable(),
                TextColumn::make('branch.name')->label(__('messages.branch') ?? 'Branch')->sortable(),
                TextColumn::make('created_at')->label(__('messages.date') ?? 'Date')->dateTime()->sortable(),
                TextColumn::make('total_amount')->label(__('messages.total') ?? 'Total')->money('inr')->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->actions([
                Action::make('view_receipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Sale $record): string => route('sales.receipt', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->striped();
    }

    public function render()
    {
        $totalPurchases = $this->customer->sales()->count();
        $totalSpent = $this->customer->sales()->where('status', 'completed')->sum('total_amount');

        return view('livewire.customers.customer-view', [
            'totalPurchases' => $totalPurchases,
            'totalSpent' => $totalSpent,
        ]);
    }
}
