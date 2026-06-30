<?php

namespace App\DTO\Bot\TelegramWebhook;

use App\Enums\Bot\MessageType;

final class TelegramMessageDTO
{
    public function __construct(
        public readonly int $chatId,
        public readonly MessageType $type,
        public readonly array $payload,
    ) {}

    public function text(): ?string
    {
        return $this->payload['message']['text']
            ?? $this->payload['message']['caption']
            ?? null;
    }

    public function callbackData(): ?string
    {
        return $this->payload['callback_query']['data'] ?? null;
    }
}
