<?php

namespace App\DTO\Bot\Scenarios\Planning;

final class NoRepeatDateDataDTO
{
    public function __construct(
        public string $dateMode,
        public ?DatePartsDTO $deadlineDate = null,
        public ?DatePartsDTO $periodStart = null,
        public ?DatePartsDTO $periodEnd = null,
    ) {}
}
