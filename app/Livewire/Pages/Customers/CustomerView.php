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

    use \App\Livewire\Pages\Sales\Concerns\HasSaleTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::where('customer_id', $this->customer->id)->latest('id'))
            ->columns($this->getSaleTableColumns())
            ->filters($this->getSaleTableFilters())
            ->actions($this->getSaleTableActions())
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->striped();
    }

    public function render()
    {
        $totalPurchases = $this->customer->sales()->count();
        $totalSpent = $this->customer->sales()->where('payment_status', 'paid')->sum('total_amount');

        return view('livewire.customers.customer-view', [
            'totalPurchases' => $totalPurchases,
            'totalSpent' => $totalSpent,
        ]);
    }
}
