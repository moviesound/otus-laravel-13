<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class TimePeriodDTO
{
    public function __construct(
        public ?string $timeStart = null,
        public ?string $timeEnd = null,
    ) {}

    public function isEmpty(): bool
    {
        return empty($this->timeStart) && empty($this->timeEnd);
    }

    public function isDeadlineOnly(): bool
    {
        return !empty($this->timeStart) && empty($this->timeEnd);
    }

    public function isPeriod(): bool
    {
        return !empty($this->timeStart) && !empty($this->timeEnd);
    }
}
