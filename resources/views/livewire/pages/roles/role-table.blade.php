<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Role Card - Admin -->
    @foreach ($roles as $role)
        <div
            class="role-card bg-surface dark:bg-surface-dark rounded-xl shadow overflow-hidden border border-border dark:border-border-dark flex flex-col justify-between">
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-semibold text-text dark:text-text-dark">{{ $role->name }}</h3>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline">
                            <x-fas-edit class="w-4 h-4" />
                        </x-ui.button>
                        <x-ui.button variant="danger">
                            <x-fas-trash class="w-4 h-4" />
                        </x-ui.button>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-text-muted dark:text-text-muted-dark mb-2">Assigned Permissions:
                    </h4>
                    @php

                        $permissions = $role->permissions->pluck('name')->toArray();
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach ($permissions as $permission)
                            <span
                                class="permission-chip border bg-chip-primary-bg dark:bg-chip-primary-bg-dark text-chip-primary-text dark:text-chip-primary-text-dark border-chip-primary-border dark:border-chip-primary-border-dark text-xs px-3 py-1 rounded-full">{{ $permission }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="bg-surface-dark/5 dark:bg-surface/5 px-5 py-3 text-right">
                <span class="text-xs text-text-muted dark:text-text-muted-dark">{{ $role->users()->count() }} {{ Str::plural('user', $role->users()->count()) }} assigned</span>
            </div>
        </div>
    @endforeach
</div>
