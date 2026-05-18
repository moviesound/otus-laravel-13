<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ActionLogController;

Route::get('/logs', [ActionLogController::class, 'index'])
    ->name('logs.index')
    ->middleware('permission:logs.view');
