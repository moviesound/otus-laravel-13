<?php

namespace App\DTO\Bot\Scenarios\Planning;

final class MonthlyDateDataDTO
{
    /**
     * @param string $monthDays
     * @param null|TimePeriodDTO $monthlyCommonTime
     * @param MonthlyTimeRuleDTO[]|null $monthlyDifferentTime
     */
    public function __construct(
        public string $monthDays,
        public ?TimePeriodDTO $monthlyCommonTime = null,
        public ?array $monthlyDifferentTime = null,
    ) {}
}
