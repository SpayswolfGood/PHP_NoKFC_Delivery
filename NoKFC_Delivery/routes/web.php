<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/crm');
});

Route::get('/dashboard', function () {
    return redirect('/crm');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('/crm', 'crm')->name('crm');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
