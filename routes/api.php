<?php

use App\Http\Controllers\AuthController;
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