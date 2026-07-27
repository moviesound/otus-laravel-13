<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class QuarterlyDateDataDTO
{
    public function __construct(
        public string $quarterType, // deadline | period
        public ?int   $monthInQuarter = null,
        public ?int   $dayInQuarter = null,
        public ?int   $startMonthInQuarter = null,
        public ?int   $startDayInQuarter = null,
        public ?int   $endMonthInQuarter = null,
        public ?int   $endDayInQuarter = null,
    )
    {
    }
}
