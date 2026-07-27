<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class MonthlyTimeRuleDTO
{
    public function __construct(
        public string $days, // "5" или "10-12"
        public TimePeriodDTO $time,
    ) {}
}
