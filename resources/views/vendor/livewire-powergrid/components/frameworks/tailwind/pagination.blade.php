<div class="items-center justify-between sm:flex gap-2" wire:loading.class="blur-[2px]" wire:target="loadMore">
    <div class="items-center justify-between w-full sm:flex-1 sm:flex">
        @if ($recordCount === 'full')
            <div @class(['mr-3' => $paginator->hasPages()])>
                <div @class([
                    'mr-2' => $paginator->hasPages(),
                    'leading-5 text-sm text-center text-text dark:text-text-dark sm:text-right',
                ])>
                    {{ trans('livewire-powergrid::datatable.pagination.showing') }}
                    <span class="font-semibold firstItem">{{ $paginator->firstItem() }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.to') }}
                    <span class="font-semibold lastItem">{{ $paginator->lastItem() }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.of') }}
                    <span class="font-semibold total">{{ $paginator->total() }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.results') }}
                </div>
            </div>
        @elseif($recordCount === 'short')
            <div @class(['mr-3' => $paginator->hasPages()])>
                <p @class([
                    'mr-2' => $paginator->hasPages(),
                    'leading-5 text-center text-text dark:text-text-dark sm:text-right',
                ])>
                    <span class="font-semibold firstItem"> {{ $paginator->firstItem() }}</span>
                    -
                    <span class="font-semibold lastItem"> {{ $paginator->lastItem() }}</span>
                    |
                    <span class="font-semibold total"> {{ $paginator->total() }}</span>
                </p>
            </div>
        @elseif($recordCount === 'min')
            <div @class(['mr-3' => $paginator->hasPages()])>
                <p @class([
                    'mr-2' => $paginator->hasPages(),
                    'leading-5 text-center text-text dark:text-text-dark sm:text-right',
                ])>
                    <span class="font-semibold firstItem"> {{ $paginator->firstItem() }}</span>
                    -
                    <span class="font-semibold lastItem"> {{ $paginator->lastItem() }}</span>
                </p>
            </div>
        @endif

        @if ($paginator->hasPages() && !in_array($recordCount, ['min', 'short']))
            <nav role="navigation" aria-label="Pagination Navigation" class="items-center justify-between sm:flex">
                <div class="flex justify-center mt-2 md:flex-none md:justify-end sm:mt-0">

                    @if (!$paginator->onFirstPage())
                        <a class="cursor-pointer relative inline-flex items-center px-2 py-2 text-sm font-medium text-primary dark:text-primary-dark bg-surface dark:bg-surface-dark border border-border dark:border-border-dark leading-5 hover:bg-surface-dark/10 dark:hover:bg-surface/10 focus:z-10 focus:outline-none focus:shadow-outline-blue active:bg-primary-100 active:text-white active:dark:text-white  ease-in-out  rounded-l-md"
                            wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                            </svg>
                        </a>
                    @else
                        <span
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-muted dark:text-text-muted-dark bg-surface dark:bg-surface-dark border border-border dark:border-border-dark rounded-l-md cursor-not-allowed opacity-50">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                            </svg>
                        </span>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span
                                        class="relative z-10 inline-flex items-center px-3 py-2 -ml-px text-sm font-bold text-white bg-primary dark:text-surface-dark dark:bg-primary-dark border border-border dark:border-border-dark cursor-default select-none">{{ $page }}</span>
                                @elseif (
                                    $page === $paginator->currentPage() + 1 ||
                                        $page === $paginator->currentPage() + 2 ||
                                        $page === $paginator->currentPage() - 1 ||
                                        $page === $paginator->currentPage() - 2)
                                    <a class="select-none cursor-pointer relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-primary dark:text-primary-dark bg-surface dark:bg-surface-dark border border-border dark:border-border-dark leading-5 hover:bg-surface-dark/10 dark:hover:bg-surface/10 focus:z-10 focus:outline-none focus:shadow-outline-blue active:bg-primary-100 active:text-primary  ease-in-out "
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a @class([
                            'block' => $paginator->lastPage() - $paginator->currentPage() >= 2,
                            'hidden' => $paginator->lastPage() - $paginator->currentPage() < 2,
                            'select-none cursor-pointer relative inline-flex items-center px-2 py-2 text-sm font-medium text-primary dark:text-primary-dark bg-surface dark:bg-surface-dark border border-border dark:border-border-dark leading-5 hover:bg-surface-dark/10 dark:hover:bg-surface/10 focus:z-10 focus:outline-none focus:shadow-outline-blue active:bg-primary-100 active:text-primary  ease-in-out ',
                        ]) wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            rel="next">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                        <a class="select-none cursor-pointer relative inline-flex items-center px-2 py-2 text-sm font-medium text-primary dark:text-primary-dark bg-surface dark:bg-surface-dark border border-border dark:border-border-dark rounded-r-md leading-5 hover:bg-surface-dark/10 dark:hover:bg-surface/10 focus:z-10 focus:outline-none focus:shadow-outline-blue active:bg-primary-100 active:text-primary  ease-in-out "
                            wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $paginator->getPageName() }}')">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    @endif
                </div>
            </nav>
        @endif
        <div>
            {{-- No changes to minimal pagination since it uses generic color already tied to tokens --}}
        </div>
    </div>
</div>
