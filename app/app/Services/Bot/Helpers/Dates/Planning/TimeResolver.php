<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\User\UserDefaultTimeDTO;
use Carbon\CarbonImmutable;

final class TimeResolver
{
    /**
     * @param string $type morning|evening
     *
     * @return array{int, int}
     */
    public function defaultHourMinuteForDate(
        CarbonImmutable $date,
        UserDefaultTimeDTO $defaultTime,
        string $type,
    ): array {
        $isHoliday = (int) $date->format('N') >= 6;

        $fallback = $type === 'morning'
            ? '08:00'
            : '21:00';

        $time = match ([$type, $isHoliday]) {
            ['morning', false] => $defaultTime->morningWorkdays,
            ['morning', true]  => $defaultTime->morningHolidays,
            ['evening', false] => $defaultTime->eveningWorkdays,
            ['evening', true]  => $defaultTime->eveningHolidays,
            default => $fallback,
        };

        return $this->parseTime($time, $fallback);
    }

    /**
     * $type: start|end
     */
    public function getTimeByDate(
        CarbonImmutable    $date,
        UserDefaultTimeDTO $defaultTime,
        string             $type,
    ): string
    {
        $isHoliday = (int)$date->format('N') >= 6;

        if ($type === 'start') {
            return $isHoliday
                ? ($defaultTime->morningHolidays ?? '09:00')
                : ($defaultTime->morningWorkdays ?? '08:00');
        }

        return $isHoliday
            ? ($defaultTime->eveningHolidays ?? '22:00')
            : ($defaultTime->eveningWorkdays ?? '21:00');
    }

    /**
     * @return array{0:int,1:int}
     */
    public function parseTime(?string $time, string $default = '00:00'): array
    {
        $time = $time ?: $default;

        $parts = explode(':', $time);

        return [
            (int)($parts[0] ?? 0),
            (int)($parts[1] ?? 0),
        ];
    }
}
