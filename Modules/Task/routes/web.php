<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\TaskController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('task', TaskController::class)->names('task');
});
