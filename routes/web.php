<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
});

Route::get('/properti', [App\Http\Controllers\Public\ListingController::class, 'index'])->name('public.listings.index');
Route::get('/properti/{listing}', [App\Http\Controllers\Public\ListingController::class, 'show'])->name('public.listings.show');

if (! app()->environment('production')) {
    Route::view('/design-system', 'design-system')->name('design-system');
}

Route::middleware(['auth', 'role:'.App\Models\User::ROLE_ADMIN.','.App\Models\User::ROLE_AGEN])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
