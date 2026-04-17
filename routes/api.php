<?php

use App\Http\Controllers\Api\Auth\EmailAuthController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    // --- Authentification ---
    Route::prefix('auth')->middleware('throttle:60,1')->group(function () {
        
        // Canal OTP (Téléphone)
        Route::post('otp/send',   [OtpController::class, 'send'])->name('auth.otp.send');
        Route::post('otp/verify', [OtpController::class, 'verify'])->name('auth.otp.verify');

        // Canal Email
        Route::post('login',    [EmailAuthController::class, 'login'])->name('auth.login');
        Route::post('register', [EmailAuthController::class, 'register'])->name('auth.register');

        // Session protégée
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me',      [SessionController::class, 'me'])->name('auth.me');
            Route::post('logout', [SessionController::class, 'logout'])->name('auth.logout');
        });
    });

    // --- Autres ressources (Phase B+) ---
    // Route::apiResource('users', UserController::class);
});
