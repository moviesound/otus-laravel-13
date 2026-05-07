<?php

namespace App\DTO;

final class SysTextUpdateDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $alias,
        public readonly string $context,
    ) {}
}
