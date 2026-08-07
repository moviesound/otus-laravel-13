<?php

namespace App\Providers;

use App\Events\Bot\Reminders\ReminderEvent;
use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use App\Listeners\Bot\Reminders\ProcessReminderListener;
use App\Listeners\Bot\TelegramWebhook\LogTelegramMessageListener;
use App\Listeners\Bot\TelegramWebhook\ProcessTelegramMessageListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TelegramMessageEvent::class => [
            LogTelegramMessageListener::class,
            ProcessTelegramMessageListener::class,
        ],
        ReminderEvent::class => [
            ProcessReminderListener::class,
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
