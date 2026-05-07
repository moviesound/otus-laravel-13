<?php

namespace App\DTO;

final class SysTextSearchDTO
{
    public function __construct(
        public readonly ?string $alias,
        public readonly int $perPage,
        public readonly string $lang,
    ) {}
}
