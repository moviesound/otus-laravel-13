<?php

namespace App\DTO;

final class SysTextStoreDTO
{
    public function __construct(
        public readonly string $alias,
        public readonly string $context,
        public readonly string $lang,
    ) {}
}
