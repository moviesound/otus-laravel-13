<?php

namespace App\Services\Bot\Helpers\Dates;

use App\DTO\Bot\User\UserDTO;
use Carbon\Carbon;

final class TimeResolver
{
    /**
     * Возвращает дефолтное время для конкретной даты
     *
     * @return array ['hour' => int, 'minute' => int, 'time_set_by_user' => int]
     */
    public function resolve(UserDTO $user, array $date): array
    {
        $carbon = $this->toCarbon($date);

        $isHoliday = $this->isHoliday($carbon);

        $time = $this->getUserTime($user, $isHoliday);

        [$hour, $minute] = $this->parseTime($time);

        return [
            'hour' => $hour,
            'minute' => $minute,
        ];
    }

    /**
     * Carbon из массива даты
     */
    private function toCarbon(array $date): Carbon
    {
        return Carbon::create(
            $date['year'] ?? now()->year,
            $date['month'] ?? 1,
            $date['day'] ?? 1,
        );
    }

    /**
     * Выходной или нет
     */
    private function isHoliday(Carbon $date): bool
    {
        return (int) $date->format('N') >= 6; // 6-7 = weekend
    }

    /**
     * Берём нужное время пользователя
     */
    private function getUserTime(UserDTO $user, bool $isHoliday): string
    {
        return $isHoliday
            ? $user->eveningTimeHolidays
            : $user->eveningTimeWorkdays;
    }

    /**
     * "18:30" → [18, 30]
     */
    private function parseTime(string $time): array
    {
        $parts = explode(':', $time);

        return [
            (int) ($parts[0] ?? 0),
            (int) ($parts[1] ?? 0),
        ];
    }
}
