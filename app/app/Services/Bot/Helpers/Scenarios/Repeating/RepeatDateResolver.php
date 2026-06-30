<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating;

use App\DTO\Bot\User\UserDTO;
use Carbon\Carbon;

final readonly class RepeatDateResolver
{
    public function __construct(
        private TimeResolver $timeResolver,
    )
    {
    }

    public function resolveNextDate(
        array   $date,
        UserDTO $user,
        int     $interval
    ): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        $baseDate = $this->createBaseDate(
            $date,
            $user
        );

        $now = Carbon::now(
            $user->timezone
        );

        if ($baseDate->greaterThan($now)) {
            return $baseDate;
        }

        $diffDays = $baseDate->diffInDays($now);

        $intervalsPassed =
            (int)floor($diffDays / $interval) + 1;

        return $baseDate->copy()->addDays(
            $intervalsPassed * $interval
        );
    }

    private function createBaseDate(
        array   $date,
        UserDTO $user
    ): Carbon
    {
        $year = $date['year'] ?? null;

        if (!$year) {
            $year = now(
                $user->timezone
            )->year;

            $candidate = Carbon::create(
                $year,
                $date['month'],
                $date['day'],
                $date['hour'] ?? 23,
                $date['minute'] ?? 59,
                0,
                $user->timezone
            );

            if ($candidate->isPast()) {
                $year++;
            }
        }

        $baseDate = Carbon::create(
            $year,
            $date['month'],
            $date['day'],
            $date['hour'] ?? 0,
            $date['minute'] ?? 0,
            0,
            $user->timezone
        );

        if (
            !isset($date['hour']) ||
            !isset($date['minute'])
        ) {
            $time = $this->timeResolver->resolve(
                $user,
                $date
            );

            $baseDate->setTime(
                $time['hour'],
                $time['minute']
            );
        }

        return $baseDate;
    }
}
