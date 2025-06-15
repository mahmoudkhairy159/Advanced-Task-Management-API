<?php

use Illuminate\Support\Facades\Route;
use Modules\UserNotification\App\Http\Controllers\Admin\UserNotificationController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::prefix('v1')->name('admin-api.')->group(function () {
    // user-notifications routes

    Route::controller(UserNotificationController::class)->name('user-notifications.')->prefix('/user-notifications')->group(function () {
        Route::get('/user/{user_id}', 'getByUserId')->name('getByUserId');
        Route::get('/notification-types', 'getUserNotificationTypes')->name('getUserNotificationTypes');

    });
    Route::apiResource('user-notifications', UserNotificationController::class);
    // user-notifications routes

});
