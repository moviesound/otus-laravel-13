<?php

namespace App\Http\Controllers\Bot\TelegramWebhook;

use App\Events\TelegramMessageEvent;
use App\Http\Controllers\Bot\TelegramWebhook\Requests\TelegramWebhookRequest;
use Illuminate\Http\Response;

class TelegramWebhookController
{
    public function __invoke(TelegramWebhookRequest $request): Response
    {
        TelegramMessageReceived::dispatch(
            $request->all()
        );

        return response('ok');
    }
}
