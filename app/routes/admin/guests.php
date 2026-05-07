<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::get('/login', [AuthController::class, 'show'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt');

Route::get('/', function () {
    return redirect('/login');
});
