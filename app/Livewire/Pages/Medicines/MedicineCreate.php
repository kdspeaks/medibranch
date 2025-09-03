<?php

namespace App\Livewire\Pages\Medicines;

use App\Livewire\Pages\Medicines\Concerns\HasMedicineForm;
use Livewire\Attributes\On;
use Livewire\Component;
// use Filament\Actions\Action;
use App\Models\Medicine;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;

class MedicineCreate extends Component implements HasForms
{

    use InteractsWithForms, HasMedicineForm;
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function resetForm()
    {
        $this->form->fill();
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

        return $this->redirect(route('medicines.list'), navigate: true); // Update this route to your actual list page
    }

    public function submitAndCreate()
    {
        $this->submit();

        $this->form->fill(); // Reset the form for creating another medicine
        $this->dispatch('scroll-to-top');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->medicineFormSchema())
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-create');
    }
}
