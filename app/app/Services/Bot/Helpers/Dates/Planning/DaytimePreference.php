<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use Carbon\CarbonImmutable;

final class DaytimePreference
{
    /**
     * Применяет предпочтительное дневное время 12:00,
     * если оно попадает в разрешённое окно.
     */
    public function apply(
        CarbonImmutable $candidate,
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
    ): CarbonImmutable {
        $hour = (int) $candidate->format('H');

        /**
         * Если пользователь явно ночной — не трогаем.
         */
        if (
            (int) $windowStart->format('H') >= 18
            || (int) $windowEnd->format('H') <= 6
        ) {
            return $candidate;
        }

        /**
         * Если уже днём — ок.
         */
        if ($hour >= 12 && $hour <= 18) {
            return $candidate;
        }

        $preferred = $candidate->setTime(12, 0, 0);

        if ($preferred >= $windowStart && $preferred <= $windowEnd) {
            return $preferred;
        }

        return $candidate;
    }
}
