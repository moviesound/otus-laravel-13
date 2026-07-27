<?php

namespace App\DTO\Bot\User;

final readonly class UserSocialDTO
{
    public function __construct(
        public int $id,
        public int $userId,

        public string $type,
        public ?string $socialId,

        public int $isMain,
        public int $keyboard,
        public ?int $currentFolderS3,
    ) {}
}
