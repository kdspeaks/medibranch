<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Roles\Assign;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Users\UserList;
use Illuminate\Support\Facades\Session;
use App\Livewire\Pages\Roles\PermissionList;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__ . '/auth.php';

Route::get('/', fn() => redirect()->route('login'));


Route::get('dashboard', Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'pages.profile')
    ->middleware(['auth'])
    ->name('profile');



Route::view('/style-guide', 'style-guide')->name('style.guide');

Route::get('/lang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
});

Route::get('/users', UserList::class)->middleware(['auth', 'verified'])->name('users');

Route::get('/roles', function () {
    return view('pages.roles.roles');
})->middleware(['auth', 'verified'])->name('roles');
Route::get('/permissions', PermissionList::class)->middleware(['auth', 'verified'])->name('permissions');
