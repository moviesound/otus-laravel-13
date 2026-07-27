<?php

use App\Http\Controllers\Bot\TelegramWebhook\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('api.welcome');
});

Route::match(
    ['get', 'post'],
    '/integrations/telegram/' . config('services.telegram.url_key'),
    TelegramWebhookController::class
)->name('telegram.webhook');
