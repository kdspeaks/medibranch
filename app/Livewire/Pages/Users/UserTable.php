<?php

namespace App\Livewire\Pages\Users;

use Livewire\Attributes\On;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'UserTable';

    public string $search = '';
    public array $checkboxValues = [];
    public bool $checkboxAll = false;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with('roles');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            // ->add('id')
            ->add('name_email', fn($user) => Blade::render((
                'components.datatable.name'
            ), ['name' => $user->name]))
            ->add('role', fn(User $user) => $user->getRoleNames()->first() ?? '—')
            ->add('email');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),
            Column::make('Name', 'name_email', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Role', 'role')
                ->sortable(),


            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    #[On('open-edit-modal')]
    public function openEditModal(int $userId): void
    {
        // $this->js("alert(". $userId .")");
        $this->js("window.dispatchEvent(new CustomEvent('open-modal', {
        detail: {
            id: 'edit-user-modal',
            payload: { userId: $userId }
        }
    }))");
    }


    // public function actionsFromView($row): View
    // {
    //     return view('components.datatable.action', ['userId' => $row->id]);
    // }

    public function actions(User $row): array
    {
        if (auth()->user()->id !== $row->id) {
            return [
                Button::add('change-role')
                    ->slot('Change Role')
                    ->id()
                    ->dispatchTo('components.ui.modal.change-role', 'edit-user-modal', ['userId' => $row->id])

            ];
        }
        return [];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
