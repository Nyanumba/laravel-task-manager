<?php

use App\Http\Controllers\TaskViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('tasks.index'));
Route::get('/tasks',        [TaskViewController::class, 'index'])->name('tasks.index');
Route::get('/tasks/report', [TaskViewController::class, 'report'])->name('tasks.report.view');
Route::get('/api-docs',     [TaskViewController::class, 'apiDocs'])->name('api.docs');