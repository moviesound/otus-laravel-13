<?php

namespace App\DTO\Bot\User;

final readonly class UserDefaultTimeDTO
{
    public function __construct(
        public string $morningWorkdays,
        public string $morningHolidays,
        public string $eveningWorkdays,
        public string $eveningHolidays,
    ) {}
}
