<?php

namespace App\DTO;

final class ActionLogStoreDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $action,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
    ) {}
}
