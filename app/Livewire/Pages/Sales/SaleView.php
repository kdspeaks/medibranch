<?php

namespace App\Livewire\Pages\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Sale Details')]
class SaleView extends Component
{
    public Sale $sale;

    public function mount(Sale $sale)
    {
        $this->sale = $sale->load(['customer', 'branch', 'user', 'items.medicine']);
    }

    public function render()
    {
        return view('livewire.sales.sale-view');
    }
}
