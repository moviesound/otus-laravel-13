<?php

namespace App\DTO\Bot\Scenarios\Planning;

final class DailyDateDataDTO
{
    public function __construct(
        public int $repeatInterval,
        public string $dateMode,
        public ?DatePartsDTO $deadlineDate = null,
        public ?DatePartsDTO $periodStart = null,
        public ?DatePartsDTO $periodEnd = null,
    ) {}
}
