<?php

namespace App\Livewire\Pages\Medicines;

use Livewire\Component;
use App\Models\Medicine;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Livewire\Pages\Medicines\Concerns\HasMedicineForm;



class MedicineEdit extends Component implements HasForms
{
    use InteractsWithForms, HasMedicineForm;
    public ?array $data = [];
    public Medicine $medicine;

    public function mount($medicine): void
    {
        // $this->medicine = Medicine::findOrFail($id);
        $this->medicine = $medicine;
        // dd($this->medicine);
        $this->setMedicine($this->medicine); // Pass to trait
        $this->form->fill($this->medicine->toArray());
    }

    public function resetForm()
    {
        $this->form->fill();
    }

    public function update(): void
    {
        $validated = $this->form->getState();

        $this->medicine->update($validated);

        Notification::make()
            ->title('Medicine Updated')
            ->body('Medicine has been successfully updated.')
            ->success()
            ->send();

        $this->redirect(route('medicines.view', ['medicine' => $this->medicine]), navigate: true); // Update this route to your actual list page
    }

    public function submit()
    {
        $this->saveMedicine();

        Notification::make()
            ->title('Medicine Created')
            ->body('Medicines have been successfully created.')
            ->success()
            ->send();
            
    }
    public function formSubmit()
    {
        $this->submit();

        return $this->redirect(route('medicines.view', ['medicine' => $this->medicine]), navigate: true); // Update this route to your actual list page
    }

    public function submitAndCreate()
    {
        $this->submit();

        $this->form->fill(); // Reset the form for creating another medicine
        $this->dispatch('scroll-to-top');
    }

    public function form(Schema $schema): Schema
    {
        return $this->medicineFormSchema($schema)
            // ->components($this->medicineFormSchema($schema))
            ->statePath('data');
    }


    public function render()
    {
        return view('livewire.pages.medicines.medicine-edit');
    }
}
