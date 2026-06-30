<?php

namespace App\Services\Bot\Helpers\Dates;

final class QuarterPeriodValidator
{
    public static function isValid(
        int $startMonth,
        int $startDay,
        int $endMonth,
        int $endDay,
    ): bool {
        if ($startMonth < $endMonth) {
            return true;
        }

        if ($startMonth > $endMonth) {
            return false;
        }

        return $startDay <= $endDay;
    }
}
