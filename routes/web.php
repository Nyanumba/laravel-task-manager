<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskViewController;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------
// Guest routes — only accessible when NOT logged in
// -------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

// -------------------------------------------------------
// Authenticated routes — only accessible when logged in
// -------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('tasks.index'));
    Route::get('/tasks',        [TaskViewController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/report', [TaskViewController::class, 'report'])->name('tasks.report.view');
    Route::get('/api-docs',     [TaskViewController::class, 'apiDocs'])->name('api.docs');
    Route::post('/logout',      [AuthController::class, 'logout'])->name('logout');
});