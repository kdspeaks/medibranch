<?php

// app/Livewire/Pages/Settings/SiteSettings.php
namespace App\Livewire\Pages\Settings;

use Livewire\Component;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class SiteSettings extends Component implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::where('key', 'site_name')->value('value'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('site_name')
                    ->label('Site Name')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function save()
    {
        Setting::updateOrCreate(
            ['key' => 'site_name'],
            ['value' => $this->data['site_name']]
        );

        cache()->forget('settings.site_name');

        // 🔥 Dispatch browser event with new value
        $this->dispatch('site-name-updated',site_name: $this->data['site_name']);

        Notification::make()
            ->title('Settings Updated')
            ->body('Site settings have been successfully updated.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.pages.settings.site-settings');
    }
}
