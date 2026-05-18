<?php

namespace App\DTO;

final class ActionLogSearchDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?int $userId,
        public readonly int $perPage,
    ) {}
}
