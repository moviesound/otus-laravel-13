<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\YearlyDateDataDTO;
use Carbon\CarbonImmutable;

final readonly class YearlyDateCalculator
{
    public function __construct(
        private DateFactory $dateFactory,
        private TimeResolver $timeResolver,
    ) {
    }

    public function calculate(
        CommonDateContextDTO $context,
        YearlyDateDataDTO $data,
    ): DateResultDTO {
        $now = $this->dateFactory->now($context->timezone);
        $year = $now->year;

        if ($data->yearType === 'deadline') {
            if ($data->monthInYear === null || $data->dayInYear === null) {
                return new DateResultDTO();
            }

            $deadline = $this->makeYearDate(
                context: $context,
                year: $year,
                month: $data->monthInYear,
                dayInMonth: $data->dayInYear,
                type: 'evening',
            );

            if ($deadline <= $now) {
                $deadline = $this->makeYearDate(
                    context: $context,
                    year: $year + 1,
                    month: $data->monthInYear,
                    dayInMonth: $data->dayInYear,
                    type: 'evening',
                );
            }

            return new DateResultDTO(deadline: $deadline);
        }

        if (
            $data->startMonthInYear === null
            || $data->startDayInYear === null
            || $data->endMonthInYear === null
            || $data->endDayInYear === null
        ) {
            return new DateResultDTO();
        }

        $periodStart = $this->makeYearDate(
            context: $context,
            year: $year,
            month: $data->startMonthInYear,
            dayInMonth: $data->startDayInYear,
            type: 'morning',
        );

        $periodEnd = $this->makeYearDate(
            context: $context,
            year: $year,
            month: $data->endMonthInYear,
            dayInMonth: $data->endDayInYear,
            type: 'evening',
        );

        if ($periodEnd <= $now) {
            $periodStart = $this->makeYearDate(
                context: $context,
                year: $year + 1,
                month: $data->startMonthInYear,
                dayInMonth: $data->startDayInYear,
                type: 'morning',
            );

            $periodEnd = $this->makeYearDate(
                context: $context,
                year: $year + 1,
                month: $data->endMonthInYear,
                dayInMonth: $data->endDayInYear,
                type: 'evening',
            );
        }

        return new DateResultDTO(
            periodStart: $periodStart,
            periodEnd: $periodEnd,
        );
    }

    private function makeYearDate(
        CommonDateContextDTO $context,
        int $year,
        int $month,
        int $dayInMonth,
        string $type,
    ): CarbonImmutable {
        /**
         * для yearly пока берём workdays-время,
         * без проверки выходной/будний.
         */
        $time = $type === 'morning'
            ? ($context->defaultTime['morning_time_workdays'] ?? '08:00')
            : ($context->defaultTime['evening_time_workdays'] ?? '21:00');

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
}
