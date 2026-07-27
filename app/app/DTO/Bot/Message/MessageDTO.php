<?php

namespace App\DTO\Bot\Message;

use App\Enums\Bot\MessageType;

final readonly class MessageDTO
{
    public function __construct(
        public MessageType $type,
        public ?string $text,
        public array $raw,
    ) {}

    public function isCallback(): bool
    {
        return $this->type === MessageType::Callback;
    }

    public function isText(): bool
    {
        return $this->text !== null;
    }

    public function value(): string
    {
        return $this->text ?? '';
    }
}
