<?php

use App\Http\Controllers\AppApi\AdminDishController;
use App\Http\Controllers\AppApi\CourierOrderController;
use App\Http\Controllers\AppApi\CustomerOrderController;
use App\Http\Controllers\AppApi\KitchenOrderController;
use App\Http\Controllers\AppApi\StaffController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->role) {
        User::ROLE_ADMIN => redirect('/crm'),
        User::ROLE_KITCHEN => redirect('/kitchen'),
        User::ROLE_COURIER => redirect('/courier'),
        default => redirect('/menu'),
    };
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('/crm', 'crm')->middleware('role:admin')->name('crm');
    Route::view('/menu', 'menu')->middleware('role:customer')->name('menu');
    Route::view('/kitchen', 'kitchen')->middleware('role:kitchen')->name('kitchen');
    Route::view('/courier', 'courier')->middleware('role:courier')->name('courier');

    Route::prefix('app-api')->group(function () {
        Route::get('/customer/dishes', [CustomerOrderController::class, 'dishes'])->middleware('role:customer');
        Route::get('/customer/orders', [CustomerOrderController::class, 'index'])->middleware('role:customer');
        Route::post('/customer/orders', [CustomerOrderController::class, 'store'])->middleware('role:customer');

        Route::get('/kitchen/orders', [KitchenOrderController::class, 'index'])->middleware('role:kitchen');
        Route::patch('/kitchen/orders/{order}/preparing', [KitchenOrderController::class, 'setPreparing'])->middleware('role:kitchen');
        Route::patch('/kitchen/orders/{order}/ready', [KitchenOrderController::class, 'setReady'])->middleware('role:kitchen');

        Route::get('/courier/orders/available', [CourierOrderController::class, 'available'])->middleware('role:courier');
        Route::get('/courier/orders/my', [CourierOrderController::class, 'myOrders'])->middleware('role:courier');
        Route::patch('/courier/orders/{order}/claim', [CourierOrderController::class, 'claim'])->middleware('role:courier');
        Route::patch('/courier/orders/{order}/deliver', [CourierOrderController::class, 'deliver'])->middleware('role:courier');

        Route::get('/admin/dishes', [AdminDishController::class, 'index'])->middleware('role:admin');
        Route::post('/admin/dishes', [AdminDishController::class, 'store'])->middleware('role:admin');
        Route::patch('/admin/dishes/{dish}', [AdminDishController::class, 'update'])->middleware('role:admin');
        Route::delete('/admin/dishes/{dish}', [AdminDishController::class, 'destroy'])->middleware('role:admin');

        Route::get('/admin/staff', [StaffController::class, 'index'])->middleware('role:admin');
        Route::post('/admin/staff', [StaffController::class, 'store'])->middleware('role:admin');
        Route::patch('/admin/staff/{user}', [StaffController::class, 'update'])->middleware('role:admin');
        Route::delete('/admin/staff/{user}', [StaffController::class, 'destroy'])->middleware('role:admin');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
