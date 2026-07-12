<?php

namespace App\Services\Bot;

use App\Contracts\Bot\Messengers\MessengerInterface;
use App\DTO\Bot\Message\MessageDTO;
use App\DTO\Bot\State\StepStateDTO;
use App\DTO\Bot\User\UserDTO;

final class BotContext
{
    public function __construct(
        public UserDTO $userDTO,
        public int $userSocialId,
        public StepStateDTO $scenarioDTO,
        public MessageDTO $messageDTO,
        public ?MessengerInterface $messenger,
        public ?bool $shouldDeleteUserMessages = true,
    ) {}
}
