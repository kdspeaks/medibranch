<?php

namespace App\Livewire\Pages\Medicines;

use Livewire\Component;
use App\Models\Medicine;
use Livewire\Attributes\On;
use Filament\Schemas\Schema;
// use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Livewire\Pages\Medicines\Concerns\HasMedicineForm;

class MedicineCreate extends Component implements HasForms, HasActions
{

    use InteractsWithForms, HasMedicineForm, InteractsWithActions;
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

    public function form(Schema $schema): Schema
    {
        return $this->medicineFormSchema($schema)
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-create');
    }
}
