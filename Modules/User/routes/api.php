<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controllers\Api\Auth\LoginController;
use Modules\User\App\Http\Controllers\Api\Auth\RegisterController;
use Modules\User\App\Http\Controllers\Api\Auth\VerificationController;
use Modules\User\App\Http\Controllers\Api\Auth\ResetPasswordController;
use Modules\User\App\Http\Controllers\Api\Auth\ForgotPasswordController;
use Modules\User\App\Http\Controllers\Api\Auth\LogoutController;
use Modules\User\App\Http\Controllers\Api\UserController;
use Modules\User\App\Http\Controllers\Api\UserFileController;

// /*
//     |--------------------------------------------------------------------------
//     | API Routes
//     |--------------------------------------------------------------------------
//     |
//     | Here is where you can register API routes for your application. These
//     | routes are loaded by the RouteServiceProvider within a group which
//     | is assigned the "api" middleware group. Enjoy building your API!
//     |
// */


Route::prefix('v1')->name('user-api.')->group(function () {
    // Auth routes
    Route::group(['prefix' => '/auth', 'name' => 'auth.'], function () {
        Route::post('/register', [RegisterController::class, 'create'])->name('create');
        Route::controller(LoginController::class)->group(function () {
            Route::post('/login', 'login')->name('login');
            Route::post('/refresh-token', 'refresh')->name('refresh-token');
        });

        Route::controller(VerificationController::class)->prefix('verification')->group(function () {
            Route::post('/verify', 'verify')->name('verification.verify');
            Route::get('/resend', 'resend')->name('verification.resend');
        });
        Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
        // forgot Password

        Route::controller(ForgotPasswordController::class)->group(function () {
            Route::post('/forgot-password', 'forgot')->name('forgot-password');
            Route::post('/forgot-password/resend-otp-code', 'resendCode')->name('forgot-password.resend-otp-code');
        });
        // forgot Password


        // Reset Password

        Route::controller(ResetPasswordController::class)->group(function () {
            Route::post('/reset-password', 'reset')->name('reset');
            Route::post('/verify-otp-code', 'verify')->name('verify');
        });
        // Reset Password

    });

    //users routes
    Route::group(['prefix' => '/users', 'name' => 'users.'], function () {

        Route::controller(UserController::class)->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('/get-recommended', 'getRecommended')->name('getRecommended');
            Route::post('/update-info', 'update')->name('update');
            Route::post('/update-user-image', 'updateUserProfileImage')->name('updateUserProfileImage');
            Route::post('/delete-user-image', 'deleteUserProfileImage')->name('deleteUserProfileImage');
            Route::post('/change-account-activity', 'changeAccountActivity')->name('changeAccountActivity');
            Route::post('/update-general-Preferences', 'updateGeneralPreferences')->name('updateGeneralPreferences');
            Route::post('/update-professional-information', 'updateProfessionalInformation')->name('updateProfessionalInformation');
            Route::post('/update-additional-information', 'updateAdditionalInformation')->name('updateAdditionalInformation');
            Route::post('/change-password', 'changePassword')->name('changePassword');
            Route::post('/update-notification-settings', 'updateNotificationSettings')->name('updateNotificationSettings');
            Route::get('/get', 'get')->name('get');
            Route::get('/{id}', 'getOneByUserId')->name('getOneByUserId');
            Route::get('/slugs/{slug}', 'showBySlug')->name('showBySlug');
            Route::post('/set-fcm-token', 'setFcmToken')->name('setFcmToken');
        });
    });
    // UserFile routes
    Route::controller(UserFileController::class)
        ->name('user-files.')
        ->prefix('user-files')
        ->group(function () {
            Route::get('/user/{user_id}', 'getByUserId')->name('getByUserId');
        });
    Route::apiResource('user-files', UserFileController::class)
        ->except(['index']);
    // End UserFile routes
});
