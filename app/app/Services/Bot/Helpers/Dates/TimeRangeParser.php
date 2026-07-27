<?php

namespace App\Services\Bot\Helpers\Dates;

final class TimeRangeParser
{
    /**
     * Парсит диапазон времени "HH:MM" или "HH:MM-HH:MM"
     *
     * @param string $input
     * @return array|false
     *  ['time_start' => 'HH:MM', 'time_end' => 'HH:MM' | null]
     */
    public static function parse(string $input): array|false
    {
        $input = trim($input);

        $pattern = '/^
            (\d{1,2}:\d{2})                  # start time
            (?:\s*[-–—]\s*(\d{1,2}:\d{2}))?  # optional end time
            $/xu';

        if (!preg_match($pattern, $input, $matches)) {
            return false;
        }

        $normalize = static function (?string $time): ?string {
            if (!$time) {
                return null;
            }

            [$h, $m] = explode(':', $time);

            return sprintf('%02d:%02d', (int)$h, (int)$m);
        };

        $timeStart = $normalize($matches[1]);
        $timeEnd   = isset($matches[2]) ? $normalize($matches[2]) : null;

        foreach ([$timeStart, $timeEnd] as $time) {
            if ($time && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
                return false;
            }
        }

        return [
            'time_start' => $timeStart,
            'time_end'   => $timeEnd,
        ];
    }
}
