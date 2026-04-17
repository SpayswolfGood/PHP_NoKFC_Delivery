<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('dishes', DishController::class);
    Route::apiResource('orders', OrderController::class);
});
