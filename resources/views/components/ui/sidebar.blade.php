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
                        Dashboard
                    </x-ui.sidebar-link>

                    {{-- Contacts --}}
                    @if (auth()->user()->can('manage-users') || auth()->user()->can('manage-suppliers'))
                        <x-ui.sidebar-dropdown title="Contacts" icon="heroicon-o-users">
                            @can('manage-suppliers')
                                <x-ui.sidebar-subitem route="medicines.suppliers">Suppliers</x-ui.sidebar-subitem>
                            @endcan

                            @can('manage-users')
                                <x-ui.sidebar-subitem route="users">Users</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Medicines --}}
                    @if (auth()->user()->can('manage-medicines') || auth()->user()->can('manage-manufacturers'))
                        <x-ui.sidebar-dropdown title="Medicines" icon="heroicon-o-beaker">
                            @can('manage-medicines')
                                <x-ui.sidebar-subitem route="medicines.list"
                                    active-consideration="medicines.create, medicines.view, medicines.edit">Medicines</x-ui.sidebar-subitem>
                            @endcan

                            @can('manage-manufacturers')
                                <x-ui.sidebar-subitem route="medicines.manufacturers">Manufacturers</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Inventory --}}
                    @if (auth()->user()->can('manage-purchases'))
                        <x-ui.sidebar-dropdown title="Inventory" icon="heroicon-o-archive-box">
                            @can('manage-purchases')
                                <x-ui.sidebar-subitem route="medicines.purchases.list"
                                    active-consideration="medicines.purchases.create">Purchases</x-ui.sidebar-subitem>
                            @endcan
                        </x-ui.sidebar-dropdown>
                    @endif

                    {{-- Roles & Permissions --}}
                    @can('manage-roles-permission')
                        <x-ui.sidebar-dropdown title="Roles & Permissions" icon="heroicon-o-shield-check">
                            <x-ui.sidebar-subitem route="roles">Roles</x-ui.sidebar-subitem>
                            <x-ui.sidebar-subitem route="permissions">Permissions</x-ui.sidebar-subitem>
                        </x-ui.sidebar-dropdown>
                    @endcan


                    <x-ui.sidebar-dropdown title="Settings" icon="heroicon-o-cog-8-tooth">
                        @can('manage-settings')
                            <x-ui.sidebar-subitem route="settings.site">
                                App Settings
                            </x-ui.sidebar-subitem>
                            <x-ui.sidebar-subitem route="medicines.taxes">Taxes</x-ui.sidebar-subitem>
                        @endcan

                        @can('manage-branches')
                            <x-ui.sidebar-subitem route="branches" icon="heroicon-o-building-storefront">
                                Branches
                            </x-ui.sidebar-subitem>
                        @endcan

                    </x-ui.sidebar-dropdown>







                </ul>


            </div>
        </div>
        <div class="hidden absolute bottom-0 left-0 justify-center p-4 space-x-4 w-full lg:flex" sidebar-bottom-menu>
            {{-- <x-ui.language-switch />
                <button type="button" data-dropdown-toggle="language-dropdown" class="inline-flex justify-center p-2 text-gray-500 rounded-sm cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white">
                  <svg class="h-5 w-5 rounded-full mt-0.5" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 3900 3900"><path fill="#b22234" d="M0 0h7410v3900H0z"/><path d="M0 450h7410m0 600H0m0 600h7410m0 600H0m0 600h7410m0 600H0" stroke="#fff" stroke-width="300"/><path fill="#3c3b6e" d="M0 0h2964v2100H0z"/><g fill="#fff"><g id="d"><g id="c"><g id="e"><g id="b"><path id="a" d="M247 90l70.534 217.082-184.66-134.164h228.253L176.466 307.082z"/><use xlink:href="#a" y="420"/><use xlink:href="#a" y="840"/><use xlink:href="#a" y="1260"/></g><use xlink:href="#a" y="1680"/></g><use xlink:href="#b" x="247" y="210"/></g><use xlink:href="#c" x="494"/></g><use xlink:href="#d" x="988"/><use xlink:href="#c" x="1976"/><use xlink:href="#e" x="2470"/></g></svg>
                </button> --}}
        </div>
    </div>
</aside>
