<?php

namespace App\Listeners\Bot\TelegramWebhook;

use App\Contracts\Bot\Messengers\MessageHandlerInterface;
use App\DTO\Bot\BotInput;
use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use App\Services\Bot\Contexts\BotContextBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Queue;

#[Queue('telegram')]
class ProcessTelegramMessageListener implements ShouldQueue
{
    const MESSENGER = 'telegram';

    public function __construct(
        private readonly MessageHandlerInterface $messageHandler,
        private readonly BotContextBuilder $botContextBuilder,
    ) {}

    public function handle(TelegramMessageEvent $event): void
    {
        $dto = $event->dto;

        $text = $dto->text() ?? $dto->callbackData();

        $input = new BotInput(
            chatId: $dto->chatId,
            messenger: self::MESSENGER,
            text: $text,
            files: [], //files will be in the future releases
        );

        $context = $this->botContextBuilder->build($input);

        $this->messageHandler->handle($context);
    }
}
