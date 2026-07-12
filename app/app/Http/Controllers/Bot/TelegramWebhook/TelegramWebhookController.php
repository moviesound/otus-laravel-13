<?php

namespace App\Http\Controllers\Bot\TelegramWebhook;

use App\DTO\Bot\TelegramWebhook\TelegramMessageDTO;
use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TelegramWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        $dto = TelegramMessageDTO::fromArray($payload);

        TelegramMessageEvent::dispatch($dto);

        return response()->json([
            'ok' => true,
        ]);
    }
}
