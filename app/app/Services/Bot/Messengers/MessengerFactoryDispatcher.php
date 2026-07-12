<?php

namespace App\Services\Bot\Messengers;

use App\Contracts\Bot\Messengers\MessengerFactoryInterface;
use App\Contracts\Bot\Messengers\MessengerInterface;
use App\Services\Bot\Messengers\TelegramWebhook\TelegramMessengerFactory;

class MessengerFactoryDispatcher implements MessengerFactoryInterface
{
    public function __construct(
        private TelegramMessengerFactory $telegram,
        //private VkMessengerFactory $vk,
    ) {}

    public function make(int|string $chatId, int $userId, string $messenger): MessengerInterface
    {
        return match ($messenger) {
            'telegram' => $this->telegram->make($chatId, $userId),
            //'vk' => $this->vk->make($chatId, $userId),
            default => throw new \InvalidArgumentException("Unknown messenger: {$messenger}")
        };
    }
}
