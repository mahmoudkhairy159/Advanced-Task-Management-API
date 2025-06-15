<?php

use Illuminate\Support\Facades\Route;
use Modules\UserNotification\App\Http\Controllers\Api\UserNotificationController;

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

Route::prefix('v1')->name('user-api.')->group(function () {


    // user-notifications routes
    Route::controller(UserNotificationController::class)->name('user-notifications.')->prefix('/user-notifications')->group(function () {
        Route::get('/my-user-notifications', 'getMyUserNotifications')->name('getMyUserNotifications');
        Route::get('/notification-types', 'getUserNotificationTypes')->name('getUserNotificationTypes');
        Route::post('{id}/mark-as-read', 'markAsRead')->name('markAsRead');
        Route::post('{id}/mark-as-unread', 'markAsUnread')->name('markAsUnread');
        Route::post('/mark-all-as-read', 'markAllAsRead')->name('markAllAsRead');
        Route::delete('{id}', 'destroy')->name('destroy');
    });
    // user-notifications routes




});
