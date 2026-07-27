<?php

namespace App\Services\Bot\Users;

use App\Contracts\Bot\Repositories\UserRepositoryInterface;
use App\Contracts\Bot\Users\UserResolverInterface;
use App\DTO\Bot\User\UserDTO;

class UserResolverService implements UserResolverInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function resolve(
        string $messenger,
        string|int $chatId
    ): UserDTO {
        // ищем существующего
        $user = $this->userRepository
            ->findBySocial(
                type: $messenger,
                socialId: $chatId
            );

        if ($user) {
            return $user;
        }

        // нет пользователя - создаём
        return $this->create(
            messenger: $messenger,
            chatId: $chatId
        );
    }


    private function create(
        string $messenger,
        string|int $chatId
    ): UserDTO {
        return $this->userRepository->createUserWithSocial(
            messenger: $messenger,
            chatId: $chatId
        );
    }
}
