<?php

namespace App\Services\Bot\TelegramWebhook;

use App\Contracts\Bot\TelegramWebhook\TelegramGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramGateway implements TelegramGatewayInterface
{
    public function post(
        string $method,
        array  $payload
    ): array
    {
        $response = Http::timeout(4)
            ->post(
                "https://api.telegram.org/bot" . config('services.telegram.api_key') . $method,
                $payload
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Telegram API error: ' . $response->body()
            );
        }

        $data = $response->json();

        return $data;
    }
}
