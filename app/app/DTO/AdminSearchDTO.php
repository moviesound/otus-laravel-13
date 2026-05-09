<?php

namespace App\DTO;

final class AdminSearchDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $name,
        public readonly int $perPage,
    ) {}
}
