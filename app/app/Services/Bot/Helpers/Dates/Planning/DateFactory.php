<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use Carbon\CarbonImmutable;
use DateTimeZone;

final class DateFactory
{
    public function safeDate(
        int                 $year,
        int                 $month,
        int                 $day,
        int                 $hour,
        int                 $minute,
        int                 $second,
        DateTimeZone|string $timezone,
    ): CarbonImmutable
    {
        $timezone = $timezone instanceof DateTimeZone
            ? $timezone
            : new DateTimeZone($timezone);

        $month = max(1, min(12, $month));

        $maxDayInMonth = $this->daysInMonth($year, $month, $timezone);

        $day = max(1, min($maxDayInMonth, $day));

        return CarbonImmutable::create(
            year: $year,
            month: $month,
            day: $day,
            hour: $hour,
            minute: $minute,
            second: $second,
            timezone: $timezone,
        );
    }

    public function daysInMonth(
        int                 $year,
        int                 $month,
        DateTimeZone|string $timezone,
    ): int
    {
        $timezone = $timezone instanceof DateTimeZone
            ? $timezone
            : new DateTimeZone($timezone);

        return CarbonImmutable::create(
            year: $year,
            month: $month,
            day: 1,
            hour: 0,
            minute: 0,
            second: 0,
            timezone: $timezone,
        )->daysInMonth;
    }

    public function now(DateTimeZone|string $timezone): CarbonImmutable
    {
        return CarbonImmutable::now($timezone);
    }
}
