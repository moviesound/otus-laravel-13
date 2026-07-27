<?php

namespace App\DTO\Bot\TelegramWebhook;

use App\Enums\Bot\MessageType;
use InvalidArgumentException;

final class TelegramMessageDTO
{
    public function __construct(
        public readonly int $chatId,
        public readonly MessageType $type,
        public readonly array $payload,
    ) {}

    public static function fromArray(array $payload): self
    {
        if (isset($payload['callback_query'])) {
            return new self(
                chatId: (int) $payload['callback_query']['message']['chat']['id'],
                type: MessageType::Callback,
                payload: $payload,
            );
        }

        if (isset($payload['message'])) {
            return new self(
                chatId: (int) $payload['message']['chat']['id'],
                type: MessageType::Message,
                payload: $payload,
            );
        }

        throw new InvalidArgumentException('Unsupported Telegram update.');
    }


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
