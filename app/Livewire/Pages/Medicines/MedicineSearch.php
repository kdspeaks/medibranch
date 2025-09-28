<?php

namespace App\Livewire\Pages\Medicines;

use App\Livewire\Pages\Purchase\PurchaseCreate;
use Livewire\Component;
use App\Models\Medicine;
use Livewire\Attributes\On;

class MedicineSearch extends Component
{
    public string $query = '';
    public $results = [];

    public function updatedQuery()
    {
        $this->results = Medicine::query()
            ->where('name', 'like', "%{$this->query}%")
            ->limit(10)
            ->get(['id', 'name', 'purchase_price', 'selling_price'])
            ->map(fn($m) => $m->toArray())
            ->toArray();
    }

    // public function mount() {
    //     dd($parent);
    // }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-search');
    }

    // #[On('clearResults')]
    // public function clearResults(): void {
    //     $this->results = [];
    // }


    // MedicineSearch.php
    public function clickItem()
    {
        // dd(1);
        // $this->dispatch('medicineSelected')
        //         ->to(PurchaseCreate::class);
    }

    // #[On('medicineSelected')]
    // public function clickItem(): void
    // {
    //     // $medicine = \App\Models\Medicine::find($id);
    //     // if (! $medicine) return;

    //     // Dispatch a BROWSER event. `detail` becomes the payload in JS.
    //     // $this->dispatch(
    //     //    'medicine-selected',
    //     // );

    //     // optional: clear local UI
    //     // $this->query = '';
    //     // $this->results = [];
    //     dd(1);
    // }
}
