<?php
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

//the api routes for this project
Route::get('/tasks/report', [TaskController::class, 'report'])->name('tasks.report');

Route::get('/tasks',                 [TaskController::class, 'index'])->name('api.tasks.index');
Route::post('/tasks',                [TaskController::class, 'store'])->name('tasks.store');
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
Route::delete('/tasks/{task}',       [TaskController::class, 'destroy'])->name('tasks.destroy');
