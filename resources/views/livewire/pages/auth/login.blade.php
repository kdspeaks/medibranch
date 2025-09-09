<?php

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    public function mount()
    {
        $this->form->email = 'kdutta494@gmail.com';
        $this->form->password = 'password';
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();
        // dd($this->form->email);

        $this->form->authenticate();

        // dump($test);

        Session::regenerate();

        $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);
    }
}; ?>

<div>
    <form class="space-y-4" wire:submit="login">
        <!-- Email Address -->
        {{-- <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input wire:model="form.email" id="email" type="email" name="email"
                   class="w-full mt-1 border-gray-300 rounded-sm form-input" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div> --}}
        <x-ui.input label="{{ __('messages.email') }}" name="email" type="email" icon="fas fa-envelope" :error="$errors->first('form.email')"
            wire:model="form.email" placeholder="username@email.comm" />

        <!-- Password -->
        {{-- <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input wire:model="form.password" id="password" type="password" name="password"
                class="w-full mt-1 border-gray-300 rounded-sm form-input" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div> --}}
        <x-ui.input label="{{ __('messages.password') }}" name="password" type="password" icon="fas fa-lock" required
            wire:model="form.password" placeholder="Password" :error="$errors->first('form.password')" />
        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            {{-- <label class="flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="border-gray-300 rounded-sm form-checkbox text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-gray-700">Remember me</span>
            </label> --}}

            <x-ui.checkbox name="remeber"  label="{{ __('messages.remember_me') }}" wire:model="form.remember" />


            @if (Route::has('password.request'))
            <a class="text-sm text-primary hover:underline dark:text-primary-dark" href="{{ route('password.request') }}" wire:navigate>
                {{ __('messages.forgot_password') }}
            </a>
            
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            
            <x-ui.button fullWidth type="submit" target="login">
                {{ __('messages.login') }}
            </x-ui.button>
            
        </div>
    </form>
</div>
