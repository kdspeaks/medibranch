<x-app-layout>
    <x-page-layout title="{{ __('messages.profile') ?? 'Profile' }}">
        <div class="py-6 space-y-6">
            <div class="p-4 sm:p-8 bg-surface dark:bg-surface-dark shadow sm:rounded-lg border border-border dark:border-border-dark">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-surface dark:bg-surface-dark shadow sm:rounded-lg border border-border dark:border-border-dark">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>
        </div>
    </x-page-layout>
</x-app-layout>
