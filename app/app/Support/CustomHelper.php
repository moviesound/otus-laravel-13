<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class CustomHelper
{
    public static function normalizeDate(mixed $date): DateTimeInterface
    {
        if ($date instanceof DateTimeInterface) {
            return $date;
        }

        if (!is_string($date)) {
            throw new \InvalidArgumentException('Date must be string or DateTimeInterface');
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Invalid date format: {$date}", 0, $e);
        }
    }

    /**
     * Generation of different_time:
     * [
     *   "5" => ["time_start" => "10:00", "time_end" => "15:00"],
     *   "2" => ["time_start" => "09:00", "time_end" => "12:00"]
     * ]
     */
    public static function makeDifferentTime(?array $days): string
    {
        if (!isset($days)) return '';

        $result = [];

        foreach ($days as $day) {
            $startHour = rand(6, 16);
            $endHour = rand($startHour + 1, 22);

            $result[(string)$day] = [
                'time_start' => sprintf('%02d:00', $startHour),
                'time_end'   => sprintf('%02d:00', $endHour),
            ];
        }

        return json_encode($result);
    }

    public static function makeCommonTime(): string
    {
        return json_encode([
            'time_start' => sprintf('%02d:00', rand(6, 14)),
            'time_end'   => sprintf('%02d:00', rand(15, 22)),
        ]);
    }

    public static function randomWeekDays(int $min = 1, int $max = 3): array
    {
        return collect(range(1, 7))
            ->random(rand($min, $max))
            ->values()
            ->toArray();
    }

    public static function randomMonthDays(int $min = 1, int $max = 3): array
    {
        return collect(range(1, 28))
            ->random(rand($min, $max))
            ->values()
            ->toArray();
    }
}
