<?php

namespace App\DTO\Bot\Scenarios\Planning;

final readonly class YearlyDateDataDTO
{
    public function __construct(
        public string $yearType, // deadline | period
        public ?int   $monthInYear = null,
        public ?int   $dayInYear = null,
        public ?int   $startMonthInYear = null,
        public ?int   $startDayInYear = null,
        public ?int   $endMonthInYear = null,
        public ?int   $endDayInYear = null,
    )
    {
    }
}
