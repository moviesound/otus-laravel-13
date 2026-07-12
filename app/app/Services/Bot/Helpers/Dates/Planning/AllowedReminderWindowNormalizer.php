<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\User\UserDefaultTimeDTO;
use Carbon\CarbonImmutable;

final readonly class AllowedReminderWindowNormalizer
{
    public function __construct(
        private DaytimePreference $daytimePreference,
    ) {
    }

    /**
     * @param UserDefaultTimeDTO $allowedTime
     */
    public function normalize(
        CarbonImmutable $candidateLocal,
        UserDefaultTimeDTO $allowedTime,
    ): CarbonImmutable {
        [$windowStart, $windowEnd] = $this->windowForDate(
            date: $candidateLocal,
            allowedTime: $allowedTime,
        );

        /**
         * Candidate уже внутри окна.
         */
        if ($candidateLocal >= $windowStart && $candidateLocal <= $windowEnd) {
            return $this->daytimePreference->apply(
                candidate: $candidateLocal,
                windowStart: $windowStart,
                windowEnd: $windowEnd,
            );
        }

        /**
         * Candidate раньше окна.
         */
        if ($candidateLocal < $windowStart) {
            return $this->daytimePreference->apply(
                candidate: $windowStart,
                windowStart: $windowStart,
                windowEnd: $windowEnd,
            );
        }

        /**
         * Candidate позже окна — переносим на следующий день
         * и пересчитываем окно именно для следующего дня.
         */
        $nextDay = $candidateLocal
            ->addDay()
            ->startOfDay();

        [$nextWindowStart, $nextWindowEnd] = $this->windowForDate(
            date: $nextDay,
            allowedTime: $allowedTime,
        );

        return $this->daytimePreference->apply(
            candidate: $nextWindowStart,
            windowStart: $nextWindowStart,
            windowEnd: $nextWindowEnd,
        );
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function windowForDate(
        CarbonImmutable $date,
        UserDefaultTimeDTO $allowedTime,
    ): array {
        $dateString = $date->format('Y-m-d');

        $startTime = $this->getTimeByDate(
            date: $date,
            allowedTime: $allowedTime,
            type: 'start',
        );

        $endTime = $this->getTimeByDate(
            date: $date,
            allowedTime: $allowedTime,
            type: 'end',
        );

        $timezone = $date->getTimezone();

        $windowStart = CarbonImmutable::parse(
            "{$dateString} {$startTime}",
            $timezone,
        );

        $windowEnd = CarbonImmutable::parse(
            "{$dateString} {$endTime}",
            $timezone,
        );

        /**
         * Окно через полночь.
         *
         * Например:
         * start = 22:00
         * end = 06:00
         */
        if ($windowEnd <= $windowStart) {
            $windowEnd = $windowEnd->addDay();
        }

        return [$windowStart, $windowEnd];
    }

    private function getTimeByDate(
        CarbonImmutable $date,
        UserDefaultTimeDTO $allowedTime,
        string $type = 'start',
    ): string {
        $dayOfWeek = (int) $date->format('N');

        $isHoliday = $dayOfWeek === 6 || $dayOfWeek === 7;

        if ($type === 'start') {
            return $isHoliday
                ? ($allowedTime->morningHolidays ?? '10:00')
                : ($allowedTime->morningWorkdays ?? '08:00');
        }

        return $isHoliday
            ? ($allowedTime->eveningHolidays ?? '18:00')
            : ($allowedTime->eveningWorkdays ?? '18:00');
    }
}
