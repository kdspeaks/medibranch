<?php

// app/Livewire/Pages/Settings/SiteSettings.php
namespace App\Livewire\Pages\Settings;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use App\Models\Branch;
use App\Models\Setting;
use Livewire\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;

class SiteSettings extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::where('key', 'site_name')->value('value'),
            'site_branch_id' => Setting::where('key', 'site_branch_id')->value('value'),
            'site_currency' => Setting::where('key', 'site_currency')->value('value') ?? '₹',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->columns(2)
                    ->schema(([
                        TextInput::make('site_name')
                            ->label(__('messages.site_name'))
                            ->required()
                            ->minLength(3)
                            ->maxLength(255),
                        Select::make('site_branch_id')
                            ->label(__('messages.site_branch'))

                            ->options(fn() => Branch::pluck('name', 'id')->toArray())
                            // ->default(setting('site_branch_id'))
                            ->searchable()
                            ->required()
                            ->hidden(!auth()->user()->can('manage-branches')),
                        TextInput::make('site_currency')
                            ->label(__('messages.currency') ?? 'Currency Symbol')
                            ->required()
                            ->maxLength(10),
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
        Setting::updateOrCreate(
            ['key' => 'site_currency'],
            ['value' => $this->data['site_currency']]
        );

        cache()->forget('settings.site_name', 'site-name');
        cache()->forget('settings.site_branch_id', 'site-branch-id');
        cache()->forget('settings.site_currency');
        cache()->forget('branch', 'site-branch-id');


        Notification::make()
            ->title(__('messages.settings_updated'))
            ->body(__('messages.settings_updated_body'))
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
