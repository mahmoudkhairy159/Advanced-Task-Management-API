<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controllers\Admin\FollowsController;
use Modules\User\App\Http\Controllers\Admin\UserBanController;
use Modules\User\App\Http\Controllers\Admin\UserController;
use Modules\User\App\Http\Controllers\Admin\UserFileController;

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


    // Users routes
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/slugs/{slug}', 'showBySlug')->name('showBySlug');
        Route::delete('{id}/delete-permanently', 'deletePermanently')->name('deletePermanently');
    });
    Route::apiResource('users', UserController::class);

    // Users routes

    // User-bans routes
    Route::controller(UserBanController::class)->prefix('user-bans')->group(function () {
        Route::get('/banned', 'getBannedUsers')->name('getBannedUsers');
        Route::get('/not-banned', 'getNotBannedUsers')->name('getNotBannedUsers');
        Route::post('/{user}/ban', 'ban')->name('ban');
        Route::post('/{user}/unban', 'unban')->name('unban');
    });

    // UserFile routes
    Route::controller(UserFileController::class)
        ->name('user-files.')
        ->prefix('user-files')
        ->group(function () {
            Route::get('/user/{user_id}', 'getByUserId')->name('getByUserId');
            Route::get('/{id}', 'show')->name('show');
        });
    // End UserFile routes
});
