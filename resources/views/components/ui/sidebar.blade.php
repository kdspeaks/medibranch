<aside x-show="showSidebar" @click.outside.window="if (window.innerWidth < 1024) showSidebar = false"
    class="fixed top-0 left-0 z-20 flex-col shrink-0 pt-16 w-64 h-full duration-75 flex transition-width"
    aria-label="Sidebar">
    <div
        class="flex relative flex-col flex-1 pt-0 min-h-0 bg-surface border-r border-border dark:bg-surface-dark dark:border-border-dark">
        <div class="flex overflow-y-auto flex-col flex-1 pt-5 pb-4">
            <div class="flex-1 px-3 space-y-1  divide-y divide-border dark:divide-border-dark">
                <ul class="space-y-2">
                    {{-- Dashboard --}}
                    <x-ui.sidebar-link route="dashboard" icon="heroicon-o-chart-pie">
                        {{ __('messages.dashboard') }}
                    </x-ui.sidebar-link>

                    {{-- Sales --}}
                    @if (auth()->user()->can('manage-sales') || auth()->user()->can('manage-pos'))
                        <x-ui.sidebar-dropdown :title="__('messages.sales')" icon="heroicon-o-shopping-cart">
                            @can('manage-pos')
                                <x-ui.sidebar-subitem route="pos">{{ __('messages.pos') }}</x-ui.sidebar-subitem>
                            @endcan
                            @can('manage-sales')
                                <x-ui.sidebar-subitem route="sales">{{ __('messages.sales_history') }}</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Contacts --}}
                    @if (auth()->user()->can('manage-users') || auth()->user()->can('manage-suppliers') || auth()->user()->can('manage-customers'))
                        <x-ui.sidebar-dropdown :title="__('messages.contacts')" icon="heroicon-o-users">
                            @can('manage-customers')
                                <x-ui.sidebar-subitem route="customers">{{ __('messages.customers') }}</x-ui.sidebar-subitem>
                            @endcan
                            @can('manage-suppliers')
                                <x-ui.sidebar-subitem route="medicines.suppliers">{{ __('messages.suppliers') }}</x-ui.sidebar-subitem>
                            @endcan

                            @can('manage-users')
                                <x-ui.sidebar-subitem route="users">{{ __('messages.users') }}</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Medicines --}}
                    @if (auth()->user()->can('manage-medicines') || auth()->user()->can('manage-manufacturers'))
                        <x-ui.sidebar-dropdown :title="__('messages.medicines')" icon="heroicon-o-beaker">
                            @can('manage-medicines')
                                <x-ui.sidebar-subitem route="medicines.list"
                                    active-consideration="medicines.create, medicines.view, medicines.edit">{{ __('messages.medicines') }}</x-ui.sidebar-subitem>
                            @endcan

                            @can('manage-manufacturers')
                                <x-ui.sidebar-subitem route="medicines.manufacturers">{{ __('messages.manufacturers') }}</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Inventory --}}
                    @if (auth()->user()->can('manage-purchases'))
                        <x-ui.sidebar-dropdown :title="__('messages.inventory')" icon="heroicon-o-archive-box">
                            @can('manage-purchases')
                                <x-ui.sidebar-subitem route="medicines.purchases.list"
                                    active-consideration="medicines.purchases.create">{{ __('messages.purchases') }}</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Roles & Permissions --}}
                    @can('manage-roles-permission')
                        <x-ui.sidebar-dropdown :title="__('messages.roles_permissions')" icon="heroicon-o-shield-check">
                            <x-ui.sidebar-subitem route="roles">{{ __('messages.roles') }}</x-ui.sidebar-subitem>
                            <x-ui.sidebar-subitem route="permissions">{{ __('messages.permissions') }}</x-ui.sidebar-subitem>
                        </x-ui.sidebar-dropdown>
                    @endcan


                    <x-ui.sidebar-dropdown :title="__('messages.settings')" icon="heroicon-o-cog-8-tooth">
                        @can('manage-settings')
                            <x-ui.sidebar-subitem route="settings.site">
                                {{ __('messages.app_settings') }}
                            </x-ui.sidebar-subitem>
                            <x-ui.sidebar-subitem route="medicines.taxes">{{ __('messages.taxes') }}</x-ui.sidebar-subitem>
                            <x-ui.sidebar-subitem route="medicines.forms">{{ __('messages.forms_units') }}</x-ui.sidebar-subitem>
                        @endcan

                        @can('manage-branches')
                            <x-ui.sidebar-subitem route="branches" icon="heroicon-o-building-storefront">
                                {{ __('messages.branches') }}
                            </x-ui.sidebar-subitem>
                        @endcan

                    </x-ui.sidebar-dropdown>







                </ul>


            </div>
        </div>
        <div class="p-4 lg:p-0 lg:px-4 lg:h-[74px] flex flex-col justify-center border-t border-border dark:border-border-dark" x-show="siteBranch !== ''"
             x-data="{ siteBranch: '{{ activeBranch()?->name ?? "" }}' }"
             x-init="window.addEventListener('branch-name-updated', e => {
                     siteBranch = e.detail.branch_name;
                 })">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 bg-primary/10 p-2 rounded-lg">
                    <x-icon name="heroicon-o-building-storefront" class="w-5 h-5 text-primary dark:text-primary-dark" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-text-muted uppercase tracking-wider dark:text-text-muted-dark">Branch</p>
                    <p class="text-sm font-semibold text-text truncate dark:text-text-dark" x-text="siteBranch"></p>
                </div>
            </div>
        </div>
    </div>
</aside>
