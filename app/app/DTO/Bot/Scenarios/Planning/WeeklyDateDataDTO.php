<?php

namespace App\DTO\Bot\Scenarios\Planning;

final class WeeklyDateDataDTO
{
    /**
     * @param int[] $weekDays
     * @param null|TimePeriodDTO $weeklyCommonTime
     * @param null|WeeklyTimeRuleDTO[] $weeklyDifferentTime
     */
    public function __construct(
        public array $weekDays,
        public ?TimePeriodDTO $weeklyCommonTime = null,
        public ?array $weeklyDifferentTime = null,
    ) {}
}
