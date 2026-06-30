<?php

namespace App\Contracts\Bot\Messengers;

interface MessengerFactoryInterface
{
    public function make(int|string $chatId, int $userId, string $messenger): MessengerInterface;
}
