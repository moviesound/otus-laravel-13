<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class PlanningCreateInputDTO
{
    public function __construct(
        public CommonDateContextDTO $context,

        public NoRepeatDateDataDTO
        |DailyDateDataDTO
        |WeeklyDateDataDTO
        |MonthlyDateDataDTO
        |QuarterlyDateDataDTO
        |YearlyDateDataDTO $dates,

        public array $reminders = [],

        public ?array $tags = null,

        public string $type,

        public string $subType,

        public string $title,

        public ?string $description = null,

        public string $action = 'add',
    ) {}
}
