<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// All API routes require authentication
Route::middleware('auth:sanctum')->group(function () {

    // Report MUST be before {task} wildcard
    Route::get('/tasks/report', [TaskController::class, 'report'])->name('tasks.report');

    Route::get('/tasks',                 [TaskController::class, 'index'])->name('tasks.index.api');
    Route::post('/tasks',                [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::delete('/tasks/{task}',       [TaskController::class, 'destroy'])->name('tasks.destroy');
});