<?php

namespace App\Listeners\Bot\TelegramWebhook;

use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Attributes\Queue;

#[Queue('telegram_logs')]
final class LogTelegramMessageListener implements ShouldQueue
{
    public function handle(TelegramMessageEvent $event): void
    {
        if (App::isProduction()) {
            return;
        }

        Log::channel('telegram-webhook')->info(
            'Telegram webhook received',
            [
                'chat_id' => $event->dto->chatId,
                'type' => $event->dto->type->value,
                'payload' => $event->dto->payload,
            ]
        );
    }
}
