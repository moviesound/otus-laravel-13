<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class WeeklyTimeRuleDTO
{
    public function __construct(
        public int $weekDay,
        public TimePeriodDTO $time,
    ) {}
}
