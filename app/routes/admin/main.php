<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/texts');
});

Route::middleware('guest:admin')->group(function () {
    include_once 'guests.php';
});

Route::middleware('auth:admin')->group(function () {

    include_once 'texts.php';

    include_once 'users.php';


    Route::post('/logout', function () {
        Auth::guard('admin')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');
});
