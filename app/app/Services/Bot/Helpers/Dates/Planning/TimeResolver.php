<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\User\UserDefaultTimeDTO;
use Carbon\CarbonImmutable;

final class TimeResolver
{
    /**
     * @param $type : morning|evening
     *
     * @return array{int, int}
     */
    public function defaultHourMinuteForDate(
        CarbonImmutable    $date,
        UserDefaultTimeDTO $defaultTime,
        string             $type,
    ): array
    {
        $isHoliday = (int)$date->format('N') >= 6;

        $key = $isHoliday
            ? "{$type}_time_holidays"
            : "{$type}_time_workdays";

        $fallback = $type === 'morning' ? '08:00' : '21:00';

        return $this->parseTime($defaultTime[$key] ?? $fallback, $fallback);
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
                ? ($defaultTime['morning_time_holidays'] ?? '09:00')
                : ($defaultTime['morning_time_workdays'] ?? '08:00');
        }

        return $isHoliday
            ? ($defaultTime['evening_time_holidays'] ?? '22:00')
            : ($defaultTime['evening_time_workdays'] ?? '21:00');
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
