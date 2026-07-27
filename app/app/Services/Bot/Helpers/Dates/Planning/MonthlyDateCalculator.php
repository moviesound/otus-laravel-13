<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\MonthlyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\TimePeriodDTO;

final readonly class MonthlyDateCalculator
{
    public function __construct(
        private DateFactory $dateFactory,
        private TimeResolver $timeResolver,
        private PeriodForDayBuilder $periodForDayBuilder,
    ) {
    }

    public function calculate(
        CommonDateContextDTO $context,
        MonthlyDateDataDTO $data,
    ): DateResultDTO {
        if (trim($data->monthDays) === '') {
            return new DateResultDTO();
        }

        $now = $this->dateFactory->now($context->timezone);

        $intervals = $this->parseIntervals(
            monthDays: $data->monthDays,
            daysInMonth: $now->daysInMonth,
        );

        if ($intervals === []) {
            return new DateResultDTO();
        }

        $found = null;

        foreach ($intervals as [$start, $end]) {
            if ($now->day <= $end) {
                $found = [$start, $end, $now->month, $now->year];
                break;
            }
        }

        if ($found === null) {
            $nextMonth = $now->addMonthNoOverflow()->startOfMonth();

            [$start, $end] = $intervals[0];

            $start = min($start, $nextMonth->daysInMonth);
            $end = min($end, $nextMonth->daysInMonth);

            $found = [$start, $end, $nextMonth->month, $nextMonth->year];
        }

        [$startDay, $endDay, $month, $year] = $found;

        $startDate = $this->dateFactory->safeDate(
            year: $year,
            month: $month,
            day: $startDay,
            hour: 0,
            minute: 0,
            second: 0,
            timezone: $context->timezone,
        );

        $endDate = $this->dateFactory->safeDate(
            year: $year,
            month: $month,
            day: $endDay,
            hour: 0,
            minute: 0,
            second: 0,
            timezone: $context->timezone,
        );

        if ($startDay !== $endDay) {
            [$hourStart, $minuteStart] = $this->timeResolver->parseTime(
                $this->timeResolver->getTimeByDate($startDate, $context->defaultTime, 'start'),
                '08:00',
            );

            [$hourEnd, $minuteEnd] = $this->timeResolver->parseTime(
                $this->timeResolver->getTimeByDate($endDate, $context->defaultTime, 'end'),
                '18:00',
            );

            return new DateResultDTO(
                periodStart: $startDate->setTime($hourStart, $minuteStart),
                periodEnd: $endDate->setTime($hourEnd, $minuteEnd, 59),
            );
        }

        return $this->periodForDayBuilder->build(
            baseDateStart: $startDate,
            baseDateEnd: $endDate,
            commonTime: $data->monthlyCommonTime,
            ruleTime: $this->ruleForMonthDay($data, $startDay),
            defaultTime: $context->defaultTime,
        );
    }

    private function ruleForMonthDay(
        MonthlyDateDataDTO $data,
        int $day,
    ): ?TimePeriodDTO {
        if (empty($data->monthlyDifferentTime)) {
            return null;
        }

        foreach ($data->monthlyDifferentTime as $rule) {
            if ($this->dayMatchesRule($day, $rule->days)) {
                return $rule->time;
            }
        }

        return null;
    }

    private function dayMatchesRule(int $day, string $rule): bool
    {
        $rule = trim($rule);

        if (str_contains($rule, '-')) {
            [$start, $end] = array_map('intval', explode('-', $rule, 2));

            return $day >= $start && $day <= $end;
        }

        return $day === (int) $rule;
    }

    private function parseIntervals(string $monthDays, int $daysInMonth): array
    {
        $intervals = [];

        foreach (explode(',', $monthDays) as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            if (str_contains($part, '-')) {
                [$start, $end] = array_map('intval', explode('-', $part, 2));

                $start = max(1, min($start, $daysInMonth));
                $end = max(1, min($end, $daysInMonth));

                if ($end < $start) {
                    $end = $start;
                }

                $intervals[] = [$start, $end];

                continue;
            }

            $day = max(1, min((int) $part, $daysInMonth));

            $intervals[] = [$day, $day];
        }

        return $intervals;
    }
}
