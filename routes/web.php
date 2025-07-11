<?php

use App\Livewire\Pages\Dashboard;

use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Roles\RoleList;
use App\Livewire\Pages\Users\UserList;
use Illuminate\Support\Facades\Session;
use App\Livewire\Pages\Branches\BranchList;
use App\Livewire\Pages\Medicines\MedicineCreate;
use App\Livewire\Pages\Medicines\MedicineList;
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

    Route::get('/branches', BranchList::class)
        ->middleware('can:manage-branches')
        ->name('branches');

    Route::get('/medicines/list', MedicineList::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.list');
    
        Route::get('/medicines/create', MedicineCreate::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.create');
});

Route::view('/style-guide', 'style-guide')->name('style.guide');

Route::get('/lang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
});
