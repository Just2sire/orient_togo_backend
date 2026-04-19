<?php

use App\Http\Controllers\Api\Auth\EmailAuthController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EstablishmentController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\GrowthSectorController;
use App\Http\Controllers\Api\SectorDataController;
use App\Http\Controllers\Api\SerieController;
use App\Http\Controllers\Api\SubjectCoefficientController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserDeviceController;
use App\Http\Controllers\Api\UserFavoriteController;
use App\Http\Controllers\Api\UserProfileController;
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
        Route::post('otp/send', [OtpController::class, 'send'])->name('auth.otp.send');
        Route::post('otp/verify', [OtpController::class, 'verify'])->name('auth.otp.verify');

        // Canal Email
        Route::post('login', [EmailAuthController::class, 'login'])->name('auth.login');
        Route::post('register', [EmailAuthController::class, 'register'])->name('auth.register');

        // Session protégée
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [SessionController::class, 'me'])->name('auth.me');
            Route::post('logout', [SessionController::class, 'logout'])->name('auth.logout');
        });
    });

    // --- Phase B: Contenu Éducatif ---
    // Routes publiques (Lecture)
    Route::get('series', [SerieController::class, 'index'])->name('series.index');
    Route::get('series/accessible', [SerieController::class, 'accessible'])->name('series.accessible');
    Route::get('series/{serie}', [SerieController::class, 'show'])->name('series.show');

    Route::get('establishments', [EstablishmentController::class, 'index'])->name('establishments.index');
    Route::get('establishments/{idOrSlug}', [EstablishmentController::class, 'show'])->name('establishments.show');

    Route::get('fields', [FieldController::class, 'index'])->name('fields.index');
    Route::get('fields/{field}', [FieldController::class, 'show'])->name('fields.show');

    Route::get('careers', [CareerController::class, 'index'])->name('careers.index');
    Route::get('careers/{career}', [CareerController::class, 'show'])->name('careers.show');

    Route::get('growth-sectors', [GrowthSectorController::class, 'index'])->name('growth-sectors.index');
    Route::get('growth-sectors/{growth_sector}', [GrowthSectorController::class, 'show'])->name('growth-sectors.show');

    // Routes protégées (Écriture - Admin/Editor)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('series', SerieController::class)->except(['index', 'show'])->parameters(['series' => 'serie']);
        Route::apiResource('subject-coefficients', SubjectCoefficientController::class)->parameters(['subject-coefficients' => 'subject_coefficient']);
        Route::apiResource('establishments', EstablishmentController::class)->except(['index', 'show'])->parameters(['establishments' => 'establishment']);
        Route::post('establishments/{establishment}/verify', [EstablishmentController::class, 'verify'])->name('establishments.verify');
        Route::apiResource('courses', CourseController::class)->parameters(['courses' => 'course']);
        Route::apiResource('careers', CareerController::class)->except(['index', 'show'])->parameters(['careers' => 'career']);
        Route::apiResource('growth-sectors', GrowthSectorController::class)->except(['index', 'show'])->parameters(['growth-sectors' => 'growth_sector']);
        Route::apiResource('sector-datas', SectorDataController::class)->parameters(['sector-datas' => 'sector_data']);
        Route::apiResource('fields', FieldController::class)->except(['index', 'show'])->parameters(['fields' => 'field']);
        Route::apiResource('tags', TagController::class)->parameters(['tags' => 'tag']);
    });

    // --- Utilisateur (Connecté) ---
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {

        // Profil & Onboarding
        Route::get('profile', [UserProfileController::class, 'show'])->name('user.profile.show');
        Route::put('profile', [UserProfileController::class, 'update'])->name('user.profile.update');
        Route::patch('profile/onboarding', [UserProfileController::class, 'completeOnboarding'])->name('user.profile.onboarding');

        // Appareils
        Route::apiResource('devices', UserDeviceController::class)->only(['index', 'store']);

        // Favoris
        Route::get('favorites', [UserFavoriteController::class, 'index'])->name('user.favorites.index');
        Route::post('favorites/toggle', [UserFavoriteController::class, 'toggle'])->name('user.favorites.toggle');
    });
});
