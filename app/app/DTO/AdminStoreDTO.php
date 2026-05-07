<?php

namespace App\DTO;

final class AdminStoreDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly array $roles,
    ) {}
}
