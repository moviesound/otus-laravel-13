<?php

use App\Models\Bot\ReminderTemplate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest:admin')->group(function () {
    require base_path('routes/admin/guests.php');
});

Route::middleware('auth:admin')->group(function () {

    require base_path('routes/admin/texts.php');

    require base_path('routes/admin/users.php');


    Route::post('/logout', function () {
        Auth::guard('admin')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');

});

Route::get('test', function () {
    $result = ReminderTemplate::query()
        ->searchText('client')
        ->get();

    return $result;
});

//Route::get('/', function () {
//    return Auth::guard('admin')->check()
//        ? redirect('/texts')
//        : redirect('/login');
//});
Route::fallback(function () {
    return auth('admin')->check()
        ? redirect('/texts')
        : redirect('/login');
});

