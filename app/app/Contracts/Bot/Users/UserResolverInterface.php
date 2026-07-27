<?php

namespace App\Contracts\Bot\Users;

use App\DTO\Bot\User\UserDTO;

interface UserResolverInterface
{
    public function resolve(
        string $messenger,
        string|int $chatId
    ): UserDTO;
}
