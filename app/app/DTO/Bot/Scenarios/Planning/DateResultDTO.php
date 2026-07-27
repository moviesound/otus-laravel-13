<?php

namespace App\DTO\Bot\Scenarios\Planning;

use DateTimeInterface;

final class DateResultDTO
{
    public function __construct(
        public ?DateTimeInterface $periodStart = null,
        public ?DateTimeInterface $periodEnd = null,
        public ?DateTimeInterface $deadline = null,
        public ?DateTimeInterface $nextReminder = null,
    ) {}
}
