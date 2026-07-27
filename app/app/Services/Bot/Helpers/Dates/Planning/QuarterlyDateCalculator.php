<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\QuarterlyDateDataDTO;
use Carbon\CarbonImmutable;

final readonly class QuarterlyDateCalculator
{
    public function __construct(
        private DateFactory $dateFactory,
        private TimeResolver $timeResolver,
    ) {
    }

    public function calculate(
        CommonDateContextDTO $context,
        QuarterlyDateDataDTO $data,
    ): DateResultDTO {
        $now = $this->dateFactory->now($context->timezone);

        $currentQuarter = (int) ceil($now->month / 3);

        $quarter = $currentQuarter;
        $year = $now->year;

        if ($data->quarterType === 'deadline') {
            if ($data->monthInQuarter === null || $data->dayInQuarter === null) {
                return new DateResultDTO();
            }

            $deadline = $this->makeQuarterDate(
                context: $context,
                year: $year,
                quarter: $quarter,
                monthInQuarter: $data->monthInQuarter,
                dayInMonth: $data->dayInQuarter,
                type: 'evening',
            );

            if ($deadline <= $now) {
                [$quarter, $year] = $this->nextQuarter($quarter, $year);

                $deadline = $this->makeQuarterDate(
                    context: $context,
                    year: $year,
                    quarter: $quarter,
                    monthInQuarter: $data->monthInQuarter,
                    dayInMonth: $data->dayInQuarter,
                    type: 'evening',
                );
            }

            return new DateResultDTO(deadline: $deadline);
        }

        if (
            $data->startMonthInQuarter === null
            || $data->startDayInQuarter === null
            || $data->endMonthInQuarter === null
            || $data->endDayInQuarter === null
        ) {
            return new DateResultDTO();
        }

        $periodStart = $this->makeQuarterDate(
            context: $context,
            year: $year,
            quarter: $quarter,
            monthInQuarter: $data->startMonthInQuarter,
            dayInMonth: $data->startDayInQuarter,
            type: 'morning',
        );

        $periodEnd = $this->makeQuarterDate(
            context: $context,
            year: $year,
            quarter: $quarter,
            monthInQuarter: $data->endMonthInQuarter,
            dayInMonth: $data->endDayInQuarter,
            type: 'evening',
        );

        if ($periodEnd <= $now) {
            [$quarter, $year] = $this->nextQuarter($quarter, $year);

            $periodStart = $this->makeQuarterDate(
                context: $context,
                year: $year,
                quarter: $quarter,
                monthInQuarter: $data->startMonthInQuarter,
                dayInMonth: $data->startDayInQuarter,
                type: 'morning',
            );

            $periodEnd = $this->makeQuarterDate(
                context: $context,
                year: $year,
                quarter: $quarter,
                monthInQuarter: $data->endMonthInQuarter,
                dayInMonth: $data->endDayInQuarter,
                type: 'evening',
            );
        }

        return new DateResultDTO(
            periodStart: $periodStart,
            periodEnd: $periodEnd,
        );
    }

    private function makeQuarterDate(
        CommonDateContextDTO $context,
        int $year,
        int $quarter,
        int $monthInQuarter,
        int $dayInMonth,
        string $type,
    ): CarbonImmutable {
        $monthInQuarter = max(1, min(3, $monthInQuarter));

        $month = $this->quarterStartMonth($quarter) + ($monthInQuarter - 1);

        /**
         * Как в legacy: для quarterly берём workdays-время,
         * без проверки выходной/будний.
         */
        $time = $type === 'morning'
            ? ($context->defaultTime['morning_time_workdays'] ?? '08:00')
            : ($context->defaultTime['evening_time_workdays'] ?? '18:00');

        [$hour, $minute] = $this->timeResolver->parseTime($time);

        return $this->dateFactory->safeDate(
            year: $year,
            month: $month,
            day: $dayInMonth,
            hour: $hour,
            minute: $minute,
            second: 0,
            timezone: $context->timezone,
        );
    }

    private function quarterStartMonth(int $quarter): int
    {
        return 1 + ($quarter - 1) * 3;
    }

    private function nextQuarter(int $quarter, int $year): array
    {
        $quarter++;

        if ($quarter > 4) {
            $quarter = 1;
            $year++;
        }

        return [$quarter, $year];
    }
}
