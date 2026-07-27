<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class DatePartsDTO
{
    public function __construct(
        public ?int $year = null,
        public ?int $month = null,
        public ?int $day = null,
        public ?int $hour = null,
        public ?int $minute = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->year === null
            && $this->month === null
            && $this->day === null
            && $this->hour === null
            && $this->minute === null;
    }

    public function hasUserTime(): bool
    {
        return $this->hour !== null || $this->minute !== null;
    }
}
