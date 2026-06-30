<?php

namespace App\Services\Bot\TelegramWebhook;

use App\Contracts\Bot\Messengers\MessengerInterface;
use App\Contracts\Bot\Repositories\TelegramRepositoryInterface;
use App\Contracts\Bot\TelegramWebhook\TelegramGatewayInterface;

final class TelegramMessengerFactory
{

    public function __construct(
        private TelegramGatewayInterface $gateway,
        private TelegramRepositoryInterface $repo,
    ) {}

    public function make(int|string $chatId, int $userId): MessengerInterface
    {
        return new TelegramMessenger(
            gateway: $this->gateway,
            repo: $this->repo,
            chatId: $chatId,
            userId: $userId,
        );
    }
}
