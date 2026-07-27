<?php

namespace Tests\Feature\Events\Bot\TelegramWebhook;

use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    public function test_dispatches_telegram_message_event(): void
    {
        Event::fake();

        $payload = [
            'update_id' => 1,
            'message' => [
                'message_id' => 1,
                'text' => '/start',
                'chat' => [
                    'id' => 123456,
                    'type' => 'private',
                ],
            ],
        ];

        $this->postJson(
            'http://api.localhost/integrations/telegram/' . config('services.telegram.url_key'),
            $payload
        );

        Event::assertDispatched(TelegramMessageEvent::class);
    }
}
