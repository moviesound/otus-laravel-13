<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\TimePeriodDTO;
use App\DTO\Bot\Scenarios\Planning\WeeklyDateDataDTO;
use Carbon\CarbonImmutable;

final readonly class WeeklyDateCalculator
{
    public function __construct(
        private DateFactory $dateFactory,
        private PeriodForDayBuilder $periodForDayBuilder,
    ) {
    }

    public function calculate(
        CommonDateContextDTO $context,
        WeeklyDateDataDTO $data,
    ): DateResultDTO {
        $weekDays = collect($data->weekDays)
            ->map(fn ($day) => (int) $day)
            ->filter(fn (int $day) => $day >= 1 && $day <= 7)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($weekDays === []) {
            return new DateResultDTO();
        }

        $now = $this->dateFactory->now($context->timezone);
        $today = (int) $now->format('N');

        [$targetDay, $daysToAdd] = $this->nearestWeekday($weekDays, $today);

        $result = $this->buildForTargetDay(
            now: $now,
            daysToAdd: $daysToAdd,
            targetDay: $targetDay,
            context: $context,
            data: $data,
        );

        if ($this->isPast($result, $now)) {
            $currentIndex = array_search($targetDay, $weekDays, true);

            $nextIndex = (
                $currentIndex === false
                || $currentIndex === count($weekDays) - 1
            )
                ? 0
                : $currentIndex + 1;

            $nextTargetDay = $weekDays[$nextIndex];

            $daysToAdd = $nextTargetDay > $today
                ? $nextTargetDay - $today
                : 7 - $today + $nextTargetDay;

            $result = $this->buildForTargetDay(
                now: $now,
                daysToAdd: $daysToAdd,
                targetDay: $nextTargetDay,
                context: $context,
                data: $data,
            );
        }

        return $result;
    }

    private function buildForTargetDay(
        CarbonImmutable $now,
        int $daysToAdd,
        int $targetDay,
        CommonDateContextDTO $context,
        WeeklyDateDataDTO $data,
    ): DateResultDTO {
        $baseStart = $now->addDays($daysToAdd)->startOfDay();
        $baseEnd = $baseStart;

        return $this->periodForDayBuilder->build(
            baseDateStart: $baseStart,
            baseDateEnd: $baseEnd,
            commonTime: $data->weeklyCommonTime,
            ruleTime: $this->ruleForWeekDay($data, $targetDay),
            defaultTime: $context->defaultTime,
        );
    }

    private function ruleForWeekDay(
        WeeklyDateDataDTO $data,
        int $targetDay,
    ): ?TimePeriodDTO {
        if (empty($data->weeklyDifferentTime)) {
            return null;
        }

        foreach ($data->weeklyDifferentTime as $rule) {
            if ($rule->weekDay === $targetDay) {
                return $rule->time;
            }
        }

        return null;
    }

    private function nearestWeekday(array $weekDays, int $today): array
    {
        foreach ($weekDays as $day) {
            if ($day >= $today) {
                return [$day, $day - $today];
            }
        }

        $targetDay = $weekDays[0];

        return [$targetDay, 7 - $today + $targetDay];
    }

    private function isPast(DateResultDTO $result, CarbonImmutable $now): bool
    {
        if ($result->periodEnd !== null) {
            return $result->periodEnd <= $now;
        }

        if ($result->deadline !== null) {
            return $result->deadline <= $now;
        }

        return false;
    }
}
