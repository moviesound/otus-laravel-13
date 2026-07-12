<?php

namespace App\Providers;

use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use App\Listeners\Bot\TelegramWebhook\LogTelegramWebhookListener;
use App\Listeners\Bot\TelegramWebhook\ProcessTelegramMessageListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TelegramMessageEvent::class => [
            LogTelegramWebhookListener::class,
            ProcessTelegramMessageListener::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
