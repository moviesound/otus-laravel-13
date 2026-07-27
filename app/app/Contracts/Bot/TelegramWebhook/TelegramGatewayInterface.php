<?php

namespace App\Contracts\Bot\TelegramWebhook;

interface TelegramGatewayInterface
{
    public function post(
        string $method,
        array  $payload
    ): array;
}
