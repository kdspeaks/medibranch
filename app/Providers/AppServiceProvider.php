<?php

namespace App\Providers;

use Livewire\Livewire;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use session value or fallback to default
        $locale = Session::get('locale', Config::get('app.locale'));

        App::setLocale($locale);
        // Optional: Set locale for Livewire response
        // Livewire::setUpdateLocale($locale);

        FilamentColor::register([
            'primary' => Color::generateV3Palette("#1976D2"),
            'danger' => Color::generateV3Palette("#D32F2F"),
        ]);

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
