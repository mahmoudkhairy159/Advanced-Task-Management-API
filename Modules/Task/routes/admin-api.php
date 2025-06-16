<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\App\Http\Controllers\Admin\TaskController;

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



    // tasks routes
    /***********Trashed tasks SoftDeletes**************/
    Route::controller(TaskController::class)->prefix('tasks')->as('tasks.')->group(function () {
        Route::get('/trashed', 'getOnlyTrashed')->name('getOnlyTrashed');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
        Route::post('/restore/{id}', 'restore')->name('restore');
    });
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    /***********Trashed tasks SoftDeletes**************/

    Route::apiResource('tasks', TaskController::class);
    // tasks routes


});