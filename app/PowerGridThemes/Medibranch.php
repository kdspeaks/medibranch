<?php

namespace app\PowerGridThemes;

use PowerComponents\LivewirePowerGrid\Themes\Tailwind;

class Medibranch extends Tailwind
{
    public string $name = 'tailwind';

    public function table(): array
    {
        return [
            'layout' => [
                'base'      => 'inline-block min-w-full align-middle',
                'div'       => 'overflow-hidden shadow-sm border-border dark:border-border-dark',
                'table'     => 'min-w-full divide-y divide-border table-fixed dark:divide-border-dark',
                'container' => 'overflow-x-auto',
                'actions'   => 'flex gap-2',
            ],

            'header' => [
                'thead'    => 'bg-text-dark/70 dark:bg-text/70 border-border dark:border-border-dark',
                'tr'       => '',
                'th'       => 'p-4 text-xs font-bold text-left text-muted-text uppercase dark:text-text-muted-dark border-border dark:border-border-dark',
                'thAction' => 'font-bold!',
            ],

            'body' => [
                'tbody'              => 'bg-surface divide-y divide-border dark:bg-surface-dark dark:divide-border-dark',
                'tbodyEmpty'         => '',
                'tr'                 => 'hover:bg-text-dark dark:hover:bg-text',
                'td'                 => 'px-4 py-4 text-sm text-text whitespace-nowrap dark:text-text-dark',
                'tdEmpty'            => 'p-2 whitespace-nowrap text-text dark:text-text-dark font-normal',
                'tdSummarize'        => 'p-2 whitespace-nowrap dark:text-text-dark text-sm text-text text-right space-y-2',
                'trSummarize'        => '',
                'tdFilters'          => '',
                'trFilters'          => '',
                'tdActionsContainer' => 'flex gap-2',
            ],
        ];
    }

    public function footer(): array
    {
        return [
            'view'                   => $this->root() . '.footer',
            'select'                 => 'appearance-none bg-none! flex rounded-md rounded-md border py-1.5 px-4 pr-7 focus:outline-hidden sm:text-sm sm:leading-6 w-auto bg-input-bg border border-input-border text-text-muted focus:ring-primary focus:border-primary dark:bg-input-bg-dark dark:border-input-border-dark placeholder-input-placeholder dark:placeholder-input-placeholder dark:text-text-muted-dark dark:focus:ring-primary-dark dark:focus:border-primary-dark',
            'footer'                 => 'sticky right-0 bottom-0 items-center p-4 w-full bg-surface border-t border-border sm:flex sm:justify-between dark:bg-surface-dark dark:border-border-dark text-text dark:text-text-dark',
            'footer_with_pagination' => '',
        ];
    }

    public function cols(): array
    {
        return [
            'div' => 'select-none flex items-center gap-1',
        ];
    }

    public function editable(): array
    {
        return [
            'view'  => $this->root() . '.editable',
            'input' => 'focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 w-full rounded-md border-0 bg-transparent py-1.5 px-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6 w-full',
        ];
    }

    public function toggleable(): array
    {
        return [
            'view' => $this->root() . '.toggleable',
        ];
    }

    public function checkbox(): array
    {
        return [
            'th'    => 'p-4 w-4 text-base font-medium',
            'base'  => '',
            'label' => 'flex items-center',
            'input' => 'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-xs focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600',
        ];
    }

    public function radio(): array
    {
        return [
            'th'    => 'px-6 py-3 text-left text-xs font-medium text-pg-primary-500 tracking-wider',
            'base'  => '',
            'label' => 'flex items-center space-x-3',
            'input' => 'form-radio rounded-full transition ease-in-out duration-100',
        ];
    }

    public function filterBoolean(): array
    {
        return [
            'view'   => $this->root() . '.filters.boolean',
            'base'   => 'min-w-20',
            'select' => 'appearance-none bg-none! focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 w-full rounded-md border-0 bg-transparent py-1.5 px-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6 w-full',
        ];
    }

    public function filterDatePicker(): array
    {
        return [
            'base'  => '',
            'view'  => $this->root() . '.filters.date-picker',
            'input' => 'flatpickr flatpickr-input focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 w-full rounded-md border-0 bg-transparent py-1.5 px-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6 w-auto',
        ];
    }

    public function filterMultiSelect(): array
    {
        return [
            'view'   => $this->root() . '.filters.multi-select',
            'base'   => 'inline-block relative w-full',
            'select' => 'mt-1',
        ];
    }

    public function filterNumber(): array
    {
        return [
            'view'  => $this->root() . '.filters.number',
            'input' => 'w-full min-w-20 block focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 rounded-md border-0 bg-transparent py-1.5 pl-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6',
        ];
    }

    public function filterSelect(): array
    {
        return [
            'view'   => $this->root() . '.filters.select',
            'base'   => '',
            'select' => 'appearance-none bg-none! focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 rounded-md border-0 bg-transparent py-1.5 px-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6 w-full',
        ];
    }

    public function filterInputText(): array
    {
        return [
            'view'   => $this->root() . '.filters.input-text',
            'base'   => 'min-w-38',
            'select' => 'appearance-none bg-none! focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 w-full rounded-md border-0 bg-transparent py-1.5 px-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6 w-full',
            'input'  => 'focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-pg-primary-600 dark:text-pg-primary-300 text-gray-600 ring-gray-300 dark:bg-pg-primary-800 bg-white dark:placeholder-pg-primary-400 w-full rounded-md border-0 bg-transparent py-1.5 px-2 ring-0 placeholder:text-gray-400 focus:outline-hidden sm:text-sm sm:leading-6 w-full',
        ];
    }

    public function searchBox(): array
    {
        return [
            'input'      => 'bg-gray-50 border border-gray-300 text-text-muted sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-text-muted-dark dark:focus:ring-primary-500 dark:focus:border-primary-500 pl-8',
            'iconClose'  => 'text-text-muted dark:text-text-muted-dark',
            'iconSearch' => 'text-text-muted mr-2 w-5 h-5 dark:text-text-muted-dark',
        ];
    }
}
