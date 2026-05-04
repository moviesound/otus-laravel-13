<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SysTextController;

Route::get('/texts', [SysTextController::class, 'index']);

Route::get('/texts/edit/{id}', [SysTextController::class, 'edit'])
    ->name('texts.edit');

Route::put('/texts/{id}', [SysTextController::class, 'update'])
    ->name('texts.update');

Route::delete('/texts/delete/{id}', [SysTextController::class, 'destroy'])
    ->name('texts.delete');

Route::get('/texts/create', [SysTextController::class, 'create'])
    ->name('texts.create');

Route::post('/texts', [SysTextController::class, 'store'])
    ->name('texts.store');

Route::get('/', function() {
    return redirect('/texts');
});
