<?php

namespace App\DTO;

final class AdminUpdateDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly array $roles,
    ) {}
}
