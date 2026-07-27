<?php

namespace App\DTO\Bot\Scenarios\Planning;

use App\DTO\Bot\User\UserDefaultTimeDTO;

final readonly class CommonDateContextDTO
{
    public function __construct(
        public string $timezone,
        public UserDefaultTimeDTO $defaultTime,
        public string $repeatType,
    ) {}
}
