<?php

namespace App\Services\Bot\Helpers\Dates;

use App\DTO\Bot\User\UserDTO;
use Carbon\Carbon;

final class NotificationTimeResolver
{
    public function resolve(UserDTO $user, array $date): array
    {
        $carbon = $this->toCarbon($date);

        $isHoliday = $this->isHoliday($carbon);

        $time = $this->getUserTime($user, $isHoliday);

        [$hour, $minute] = $this->parseTime($time);

        return array_merge($date, [
            'hour' => $hour,
            'minute' => $minute,
            'time_set_by_user' => 0,
        ]);
    }

    private function toCarbon(array $date): Carbon
    {
        return Carbon::create(
            $date['year'] ?? now()->year,
            $date['month'] ?? 1,
            $date['day'] ?? 1,
        );
    }

    private function isHoliday(Carbon $date): bool
    {
        return (int) $date->format('N') >= 6;
    }

    private function getUserTime(UserDTO $user, bool $isHoliday): string
    {
        return $isHoliday
            ? $user->eveningTimeHolidays
            : $user->eveningTimeWorkdays;
    }

    private function parseTime(string $time): array
    {
        $parts = explode(':', $time);

        return [
            (int) ($parts[0] ?? 0),
            (int) ($parts[1] ?? 0),
        ];
    }
}
