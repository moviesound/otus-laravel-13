<?php

namespace App\DTO\Bot;

final readonly class BotInput
{
    public function __construct(
        public int|string $chatId,
        public string $messenger,
        public ?string $text,
        public array $files = [],
    ) {}
}
