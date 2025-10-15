<?php

use App\Livewire\Pages\Purchase\PurchaseEdit;
use App\Models\Tax;

use App\Livewire\Pages\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Roles\RoleList;
use App\Livewire\Pages\Users\UserList;
use Illuminate\Support\Facades\Session;
use App\Livewire\Pages\Medicines\TaxList;
use App\Livewire\Pages\Branches\BranchList;
use App\Livewire\Pages\Roles\PermissionList;
use App\Livewire\Pages\Purchase\PurchaseList;
use App\Livewire\Pages\Settings\SiteSettings;
use App\Livewire\Pages\Supplier\SupplierList;
use App\Livewire\Pages\Medicines\MedicineEdit;
use App\Livewire\Pages\Medicines\MedicineList;
use App\Livewire\Pages\Medicines\MedicineView;
use App\Livewire\Pages\Purchase\PurchaseCreate;
use App\Livewire\Pages\Medicines\MedicineCreate;
use App\Livewire\Pages\Medicines\ManufacturerList;

require __DIR__ . '/auth.php';

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', Dashboard::class)
        // ->middleware('can:view dashboard')
        ->name('dashboard');

    Route::view('profile', 'pages.profile')
        ->name('profile');

    Route::get('/users', UserList::class)
        // ->middleware('can:manage-users')
        ->name('users');

    Route::get('/roles', RoleList::class)
        // ->middleware('can:manage-roles-permission')
        ->name('roles');

    Route::get('/permissions', PermissionList::class)
        // ->middleware('can:manage-roles-permission')
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

    Route::get('/medicines/edit/{medicine}', MedicineEdit::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.edit');

    Route::get('/medicines/view/{medicine}', MedicineView::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.view');


    Route::get('/medicines/manufacturers', ManufacturerList::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.manufacturers');

    Route::get('/medicines/taxes', TaxList::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.taxes');

    Route::get('/medicines/suppliers', SupplierList::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.suppliers');

    Route::get('/medicines/purchases/list', PurchaseList::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.purchases.list');

    Route::get('/medicines/purchases/create', PurchaseCreate::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.purchases.create');

    Route::get('/medicines/purchases/view/{purchase}', PurchaseCreate::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.purchases.view');

    Route::get('/medicines/purchases/edit/{purchase}', PurchaseEdit::class)
        ->middleware('can:manage-medicines')
        ->name('medicines.purchases.edit');
});

Route::view('/style-guide', 'style-guide')->name('style.guide');

Route::get('/lang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
});
