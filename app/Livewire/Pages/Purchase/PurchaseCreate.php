<?php

namespace App\Livewire\Pages\Purchase;

use Livewire\Component;
use App\Models\Medicine;
// use Filament\Actions\Action;
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
use App\Livewire\Pages\Purchase\Concerns\HasPurchaseForm;

class PurchaseCreate extends Component implements HasForms
{

    use InteractsWithForms, HasPurchaseForm;
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function resetForm() {
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
            ->schema($this->purchaseFormSchema())
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-create');
    }
}
