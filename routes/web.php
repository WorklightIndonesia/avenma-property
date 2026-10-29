<?php

use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ListingController;
use App\Http\Controllers\Public\PageController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('public.pages.home');
Route::get('/tentang-kami', [PageController::class, 'about'])->name('public.pages.about');
Route::get('/kontak', [PageController::class, 'contact'])->name('public.pages.contact');
Route::get('/syarat-ketentuan', [PageController::class, 'terms'])->name('public.pages.terms');

Route::get('/properti', [ListingController::class, 'index'])->name('public.listings.index');
Route::get('/properti/{listing}', [ListingController::class, 'show'])->name('public.listings.show');

if (! app()->environment('production')) {
    Route::view('/design-system', 'design-system')->name('design-system');
}

Route::middleware(['auth', 'role:'.User::ROLE_ADMIN.','.User::ROLE_AGEN])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/wilayah', [WilayahController::class, 'wilayah'])
        ->middleware('throttle:120,1')
        ->name('admin.wilayah');

    Route::get('/admin/alamat', [WilayahController::class, 'alamat'])
        ->middleware('throttle:60,1')
        ->name('admin.alamat');

    Route::resource('/admin/listings', App\Http\Controllers\Admin\ListingController::class)
        ->except('show')
        ->names('admin.listings');

    Route::delete('/admin/listings/{listing}/foto/{image}', [App\Http\Controllers\Admin\ListingController::class, 'destroyPhoto'])
        ->name('admin.listings.photos.destroy');

    Route::resource('/admin/developers', DeveloperController::class)
        ->except('show')
        ->names('admin.developers');

    Route::middleware('role:'.User::ROLE_ADMIN)->group(function () {
        Route::resource('/admin/users', UserController::class)
            ->except('show')
            ->names('admin.users');

        Route::get('/admin/pages', [PageContentController::class, 'index'])->name('admin.pages.index');
        Route::get('/admin/pages/{page}/edit', [PageContentController::class, 'edit'])->name('admin.pages.edit');
        Route::put('/admin/pages/{page}', [PageContentController::class, 'update'])->name('admin.pages.update');

        Route::get('/admin/settings', [SiteSettingController::class, 'edit'])->name('admin.settings.edit');
        Route::put('/admin/settings', [SiteSettingController::class, 'update'])->name('admin.settings.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
