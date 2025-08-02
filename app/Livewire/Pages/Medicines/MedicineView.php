<?php

namespace App\Livewire\Pages\Medicines;

use Livewire\Component;
use App\Models\Medicine;

class MedicineView extends Component
{
    public Medicine $medicine;

    public function mount(Medicine $medicine)
    {
        $this->medicine = $medicine->load('manufacturer');
    }
    public function render()
    {
        return view('livewire.pages.medicines.medicine-view');
    }
}
