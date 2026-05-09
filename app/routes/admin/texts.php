<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SysTextController;

Route::get('/texts', [SysTextController::class, 'index'])
    ->middleware('permission:texts.view');;

Route::get('/texts/edit/{id}', [SysTextController::class, 'edit'])
    ->name('texts.edit')
    ->middleware('permission:texts.update');

Route::put('/texts/{id}', [SysTextController::class, 'update'])
    ->name('texts.update')
    ->middleware('permission:texts.update');

Route::delete('/texts/delete/{id}', [SysTextController::class, 'destroy'])
    ->name('texts.delete')
    ->middleware('permission:texts.delete');

Route::get('/texts/create', [SysTextController::class, 'create'])
    ->name('texts.create')
    ->middleware('permission:texts.create');

Route::post('/texts', [SysTextController::class, 'store'])
    ->name('texts.store')
    ->middleware('permission:texts.create');
