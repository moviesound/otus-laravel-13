<?php

namespace App\Contracts\Bot\Repositories;


use App\DTO\Bot\User\UserDTO;

interface UserRepositoryInterface
{
    public function getUserByChatIdAndMessengerType (string $chatId, string $messangerType): UserDTO;
    public function getUserIdByChatIdAndMessengerType (string $chatId, string $messangerType): int;
    public function getSocialUserIdByChatIdAndMessengerType(string $chatId, string $messangerType): int;
    public function agreeOnPolitics(int $userId): bool;
}
