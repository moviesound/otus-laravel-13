<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DatePartsDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\NoRepeatDateDataDTO;
use Carbon\CarbonImmutable;
use DateTimeZone;

final readonly class NoRepeatDateCalculator
{
    public function __construct(
        private DateFactory $dateFactory,
        private TimeResolver $timeResolver,
    ) {
    }

    public function calculate(
        CommonDateContextDTO $context,
        NoRepeatDateDataDTO $data,
    ): DateResultDTO {
        if ($data->dateMode === 'deadline') {
            return new DateResultDTO(
                deadline: $data->deadlineDate
                    ? $this->getNearestDate($context, $data->deadlineDate, 'evening')
                    : null,
            );
        }

        return $this->getNearestTaskPeriod($context, $data);
    }

    public function getNearestDate(
        CommonDateContextDTO $context,
        DatePartsDTO $dateParts,
        string $type = 'morning',
    ): ?CarbonImmutable {
        if ($dateParts->isEmpty()) {
            return null;
        }

        $tz = new DateTimeZone($context->timezone);
        $now = $this->dateFactory->now($tz);

        $year = $dateParts->year;
        $month = $dateParts->month ?? $now->month;
        $day = $dateParts->day ?? $now->day;

        $baseYear = $year ?? $now->year;

        $tempDate = $this->dateFactory->safeDate(
            year: $baseYear,
            month: $month,
            day: $day,
            hour: 0,
            minute: 0,
            second: 0,
            timezone: $tz,
        );

        if ($dateParts->hour === null || $dateParts->minute === null) {
            [$hour, $minute] = $this->timeResolver->defaultHourMinuteForDate(
                $tempDate,
                $context->defaultTime,
                $type,
            );
        } else {
            $hour = $dateParts->hour;
            $minute = $dateParts->minute;
        }

        $candidate = $this->dateFactory->safeDate(
            year: $baseYear,
            month: $month,
            day: $day,
            hour: $hour,
            minute: $minute,
            second: 0,
            timezone: $tz,
        );

        if ($year === null && $candidate < $now) {
            $candidate = $candidate->addYear();
        }

        return $candidate;
    }

    private function getNearestTaskPeriod(
        CommonDateContextDTO $context,
        NoRepeatDateDataDTO $data,
    ): DateResultDTO {
        if ($data->periodStart === null) {
            return new DateResultDTO();
        }

        $periodStart = $this->getNearestDate(
            $context,
            $data->periodStart,
            'morning',
        );

        $periodEnd = $this->getNearestDate(
            $context,
            $data->periodEnd ?? $data->periodStart,
            'evening',
        );

        if (
            $periodStart instanceof CarbonImmutable
            && $periodEnd instanceof CarbonImmutable
            && $periodEnd < $periodStart
        ) {
            $periodStart = $periodStart->subYear();
        }

        return new DateResultDTO(
            periodStart: $periodStart,
            periodEnd: $periodEnd,
        );
    }
}
