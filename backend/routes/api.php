<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('ping', fn () => response()->json(['message' => 'pong']))
        ->name('api.ping');

    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('api.register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('api.login');

    Route::post('password/forgot', [PasswordResetController::class, 'forgotPassword'])
        ->middleware('throttle:5,1')
        ->name('api.password.forgot');

    Route::post('password/reset', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:5,1')
        ->name('api.password.reset');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('me', [AuthController::class, 'me'])->name('api.me');

        Route::get('admin/ping', fn () => response()->json(['message' => 'ok']))
            ->middleware('role:admin')
            ->name('api.admin.ping');
    });
});
