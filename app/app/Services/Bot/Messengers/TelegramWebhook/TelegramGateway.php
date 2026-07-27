<?php

namespace App\Services\Bot\Messengers\TelegramWebhook;

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
        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->post(
                "https://" . config('services.telegram.api_domain') . "/bot" . config('services.telegram.api_key') . $method,
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
