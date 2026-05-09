<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;

// список
Route::get('/users', [AdminController::class, 'index'])
    ->middleware('permission:users.view')
    ->name('admins.index');

// форма создания
Route::get('/users/create', [AdminController::class, 'create'])
    ->middleware('permission:users.create')
    ->name('admins.create');

// создание
Route::post('/users', [AdminController::class, 'store'])
    ->middleware('permission:users.create')
    ->name('admins.store');

// форма редактирования
Route::get('/users/edit/{id}', [AdminController::class, 'edit'])
    ->middleware('permission:users.update')
    ->name('admins.edit');

// обновление
Route::put('/users/{id}', [AdminController::class, 'update'])
    ->middleware('permission:users.update')
    ->name('admins.update');

// сброс пароля
Route::post('/users/password/{id}', [AdminController::class, 'resetPassword'])
    ->middleware('permission:users.password_change')
    ->name('admins.password');

// удаление
Route::delete('/delete/{id}', [AdminController::class, 'destroy'])
    ->middleware('permission:users.delete')
    ->name('admins.delete');
