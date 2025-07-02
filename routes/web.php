<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Roles\Assign;
use App\Livewire\Pages\Users\UserList;
use App\Livewire\Pages\Roles\RoleList;
use App\Livewire\Pages\Roles\PermissionList;
use App\Livewire\Pages\Settings\SiteSettings;

require __DIR__ . '/auth.php';

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', Dashboard::class)
        // ->middleware('can:view dashboard')
        ->name('dashboard');

    Route::view('profile', 'pages.profile')
        ->name('profile');

    Route::get('/users', UserList::class)
        ->middleware('can:manage-users')
        ->name('users');

    Route::get('/roles', RoleList::class)
        ->middleware('can:manage-roles-permission')
        ->name('roles');

    Route::get('/permissions', PermissionList::class)
        ->middleware('can:manage-roles-permission')
        ->name('permissions');

    Route::get('/settings/site', SiteSettings::class)
        ->middleware('can:manage-settings')
        ->name('settings.site');
});

Route::view('/style-guide', 'style-guide')->name('style.guide');

Route::get('/lang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
});

