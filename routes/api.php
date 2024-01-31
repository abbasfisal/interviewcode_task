<?php

use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);
Route::apiResource('orders', OrderController::class)->middleware(['api', 'auth']);

Route::group(['middleware' => ['api'], 'prefix' => 'auth'], function () {
    Route::post('login', [AuthenticateController::class, 'login'])->name('login');
    Route::post('register', [AuthenticateController::class, 'register'])->name('register');
});
