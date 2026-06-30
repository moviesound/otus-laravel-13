<?php

namespace App\Events\Bot\TelegramWebhook;

use App\DTO\Bot\TelegramWebhook\TelegramMessageDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelegramMessageEvent
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly TelegramMessageDTO $dto){}
}
