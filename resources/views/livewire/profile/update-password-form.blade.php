<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-text dark:text-text-dark">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6">
        <x-ui.input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" :label="__('Current Password')" autocomplete="current-password" :error="$errors->first('current_password')" />

        <x-ui.input wire:model="password" id="update_password_password" name="password" type="password" :label="__('New Password')" autocomplete="new-password" :error="$errors->first('password')" />

        <x-ui.input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" :label="__('Confirm Password')" autocomplete="new-password" :error="$errors->first('password_confirmation')" />

        <div class="flex items-center gap-4">
            <x-ui.button type="submit" target="updatePassword">
                {{ __('Save') }}
            </x-ui.button>

            <x-action-message class="me-3" on="password-updated">
                <span class="text-sm text-green-600 dark:text-green-400">{{ __('Saved.') }}</span>
            </x-action-message>
        </div>
    </form>
</section>
