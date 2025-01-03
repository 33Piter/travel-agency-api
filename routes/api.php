<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelOrderController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::get('/user', [AuthController::class, 'getUser'])->name('auth.user');
    });
});

Route::prefix('v1')->group(function () {
    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::apiResource('travel-order', TravelOrderController::class)->except(['destroy']);
        Route::get('travel-order/notify/{travel_order}', [TravelOrderController::class, 'notify'])->name('travel-order.notify');
    });
});