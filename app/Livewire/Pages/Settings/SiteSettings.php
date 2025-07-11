<?php

// app/Livewire/Pages/Settings/SiteSettings.php
namespace App\Livewire\Pages\Settings;

use Filament\Forms;
use App\Models\Branch;
use App\Models\Setting;
use Filament\Forms\Components\Group;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class SiteSettings extends Component implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::where('key', 'site_name')->value('value'),
            'site_branch_id' => Setting::where('key', 'site_branch_id')->value('value'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->columns(2)
                    ->schema(([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Site Name')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255),
                        Select::make('site_branch_id')
                            ->label('Site Branch')

                            ->options(fn() => Branch::pluck('name', 'id')->toArray())
                            // ->default(setting('site_branch_id'))
                            ->searchable()
                            ->required()
                    ]))
            ])
            ->statePath('data');
    }

    public function save()
    {
        Setting::updateOrCreate(
            ['key' => 'site_name'],
            ['value' => $this->data['site_name']]
        );
        Setting::updateOrCreate(
            ['key' => 'site_branch_id'],
            ['value' => $this->data['site_branch_id']]
        );

        cache()->forget('settings.site_name', 'site-name');
        cache()->forget('settings.site_branch_id', 'site-branch-id');
        cache()->forget('branch', 'site-branch-id');


        Notification::make()
            ->title('Settings Updated')
            ->body('Site settings have been successfully updated.')
            ->success()
            ->send();

        // 🔥 Dispatch browser event with new value
        $this->dispatch('site-name-updated', site_name: $this->data['site_name']);
        $this->dispatch('branch-name-updated', branch_name: activeBranch()?->name);
    }

    public function render()
    {
        return view('livewire.pages.settings.site-settings');
    }
}
