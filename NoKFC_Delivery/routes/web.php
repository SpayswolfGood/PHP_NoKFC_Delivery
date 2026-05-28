<?php

use App\Http\Controllers\AppApi\AdminDishController;
use App\Http\Controllers\AppApi\AdminOrderController;
use App\Http\Controllers\AppApi\AdminStaffController;
use App\Http\Controllers\AppApi\CourierOrderController;
use App\Http\Controllers\AppApi\CustomerOrderController;
use App\Http\Controllers\AppApi\KitchenOrderController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        User::ROLE_ADMIN   => redirect('/crm'),
        User::ROLE_KITCHEN => redirect('/kitchen'),
        User::ROLE_COURIER => redirect('/courier'),
        default            => redirect('/menu'),
    };
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::view('/crm',     'crm')    ->middleware('role:admin')   ->name('crm');
    Route::view('/menu',    'menu')   ->middleware('role:customer')->name('menu');
    Route::view('/kitchen', 'kitchen')->middleware('role:kitchen') ->name('kitchen');
    Route::view('/courier', 'courier')->middleware('role:courier') ->name('courier');

    Route::prefix('app-api')->group(function () {

        // ── Customer ─────────────────────────────────────────────
        Route::middleware('role:customer')->group(function () {
            Route::get  ('/customer/dishes',                  [CustomerOrderController::class, 'dishes']);
            Route::get  ('/customer/orders',                  [CustomerOrderController::class, 'index']);
            Route::post ('/customer/orders',                  [CustomerOrderController::class, 'store']);
            Route::patch('/customer/orders/{order}/cancel',   [CustomerOrderController::class, 'cancel']);
        });

        // ── Kitchen ──────────────────────────────────────────────
// Находим секцию кухни и добавляем роуты:
Route::middleware('role:kitchen')->group(function () {
    Route::get  ('/kitchen/orders',                  [KitchenOrderController::class, 'index']);
    Route::patch('/kitchen/orders/{order}/preparing', [KitchenOrderController::class, 'setPreparing']);
    Route::patch('/kitchen/orders/{order}/ready',     [KitchenOrderController::class, 'setReady']);
    
    // Новые роуты для склада ингредиентов
    Route::get  ('/kitchen/ingredients',              [KitchenOrderController::class, 'ingredients']);
    Route::patch('/kitchen/ingredients/{ingredient}', [KitchenOrderController::class, 'toggleIngredient']);
});

        // ── Courier ──────────────────────────────────────────────
        Route::middleware('role:courier')->group(function () {
            Route::get  ('/courier/orders/available',               [CourierOrderController::class, 'available']);
            Route::get  ('/courier/orders/my',                      [CourierOrderController::class, 'myOrders']);
            Route::patch('/courier/orders/{order}/claim',           [CourierOrderController::class, 'claim']);
            Route::patch('/courier/orders/{order}/deliver',         [CourierOrderController::class, 'deliver']);
        });

        // ── Admin ────────────────────────────────────────────────
        Route::middleware('role:admin')->group(function () {

            // Dishes — POST для store и update (multipart/form-data с картинкой)
            Route::get   ('/admin/dishes',             [AdminDishController::class, 'index']);
            Route::post  ('/admin/dishes',             [AdminDishController::class, 'store']);
            Route::post  ('/admin/dishes/{dish}',      [AdminDishController::class, 'update']);   // _method=PUT в FormData
            Route::delete('/admin/dishes/{dish}/image',[AdminDishController::class, 'destroyImage']);
            Route::delete('/admin/dishes/{dish}',      [AdminDishController::class, 'destroy']);

            // Staff
            Route::get   ('/admin/staff',              [AdminStaffController::class, 'index']);
            Route::post  ('/admin/staff',              [AdminStaffController::class, 'store']);
            Route::put   ('/admin/staff/{user}',       [AdminStaffController::class, 'update']);
            Route::delete('/admin/staff/{user}',       [AdminStaffController::class, 'destroy']);

            // Orders
            Route::get   ('/admin/orders',             [AdminOrderController::class, 'index']);
            Route::get   ('/admin/orders/{order}',     [AdminOrderController::class, 'show']);
            Route::post  ('/admin/orders',             [AdminOrderController::class, 'store']);
            Route::patch ('/admin/orders/{order}',     [AdminOrderController::class, 'update']);
            Route::delete('/admin/orders/{order}',     [AdminOrderController::class, 'destroy']);

    
    // Ингредиенты на складе (Новое!)
    Route::get   ('/admin/ingredients',        [AdminDishController::class, 'getIngredients']);
    Route::post  ('/admin/ingredients',        [AdminDishController::class, 'storeIngredient']);
    Route::put   ('/admin/ingredients/{id}',   [AdminDishController::class, 'updateIngredient']);
    Route::delete('/admin/ingredients/{id}',   [AdminDishController::class, 'destroyIngredient']);
        });
    });

    Route::get   ('/profile', [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch ('/profile', [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';