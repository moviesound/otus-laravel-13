<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\CommonDateContextDTO;
use App\DTO\Bot\Scenarios\Planning\DailyDateDataDTO;
use App\DTO\Bot\Scenarios\Planning\DatePartsDTO;
use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\User\UserDefaultTimeDTO;
use Carbon\CarbonImmutable;
use DateTimeZone;

final readonly class DailyDateCalculator
{
    public function __construct(
        private DateFactory $dateFactory,
        private TimeResolver $timeResolver,
    ) {
    }

    public function calculate(
        CommonDateContextDTO $context,
        DailyDateDataDTO $data,
    ): DateResultDTO {
        if ($data->dateMode === 'deadline') {
            return new DateResultDTO(
                deadline: $data->deadlineDate
                    ? $this->getNextTaskDate(
                        context: $context,
                        dateParts: $data->deadlineDate,
                        interval: $data->repeatInterval,
                        type: 'evening',
                    )
                    : null,
            );
        }

        return $this->getNextTaskPeriod($context, $data);
    }

    public function getNextTaskDate(
        CommonDateContextDTO $context,
        DatePartsDTO $dateParts,
        int $interval,
        string $type = 'morning',
    ): ?CarbonImmutable {
        if ($dateParts->isEmpty()) {
            return null;
        }

        $interval = max(1, $interval);

        $tz = new DateTimeZone($context->timezone);
        $now = $this->dateFactory->now($tz);

        $month = $dateParts->month ?? 1;
        $day = $dateParts->day ?? 1;

        if ($dateParts->year !== null) {
            $year = $dateParts->year;
        } else {
            $year = $now->year;

            $candidateForYear = $this->dateFactory->safeDate(
                year: $year,
                month: $month,
                day: $day,
                hour: $dateParts->hour ?? 23,
                minute: $dateParts->minute ?? 59,
                second: 59,
                timezone: $tz,
            );

            if ($candidateForYear <= $now) {
                $year++;
            }
        }

        $baseDate = $this->dateFactory->safeDate(
            year: $year,
            month: $month,
            day: $day,
            hour: $dateParts->hour ?? 0,
            minute: $dateParts->minute ?? 0,
            second: 0,
            timezone: $tz,
        );


        if (!$dateParts->hasUserTime()) {
            [$hour, $minute] = $this->timeResolver->defaultHourMinuteForDate(
                $baseDate,
                $context->defaultTime,
                $type,
            );

            $baseDate = $baseDate->setTime($hour, $minute);
        }

        if ($baseDate > $now) {
            return $baseDate;
        }

        $diffDays = $baseDate->diffInDays($now);
        $intervalsPassed = (int) floor($diffDays / $interval) + 1;

        return $baseDate->addDays($intervalsPassed * $interval);
    }

    private function getNextTaskPeriod(
        CommonDateContextDTO $context,
        DailyDateDataDTO $data,
    ): DateResultDTO {
        if ($data->repeatInterval <= 0) {
            return new DateResultDTO();
        }

        $periodStart = null;
        $periodEnd = null;

        if ($data->periodStart !== null) {
            $periodStart = $this->getNextTaskDate(
                context: $context,
                dateParts: $data->periodStart,
                interval: $data->repeatInterval,
                type: 'morning',
            );
        }

        if ($data->periodEnd !== null) {
            if ($data->periodStart !== null) {
                $origStart = $this->dateFromPartsForDuration(
                    context: $context,
                    dateParts: $data->periodStart,
                    type: 'morning',
                );

                $origEnd = $this->dateFromPartsForDuration(
                    context: $context,
                    dateParts: $data->periodEnd,
                    type: 'evening',
                );

                if (
                    $origStart instanceof CarbonImmutable
                    && $origEnd instanceof CarbonImmutable
                    && $origEnd > $origStart
                    && $periodStart instanceof CarbonImmutable
                ) {
                    $durationSeconds = $origEnd->timestamp - $origStart->timestamp;

                    $periodEnd = $periodStart->addSeconds($durationSeconds);

                    [$hour, $minute] = $this->timeResolver->defaultHourMinuteForDate(
                        $periodEnd,
                        $context->defaultTime,
                        'evening',
                    );

                    $periodEnd = $periodEnd->setTime($hour, $minute);
                } else {
                    $periodEnd = $this->getNextTaskDate(
                        context: $context,
                        dateParts: $data->periodEnd,
                        interval: $data->repeatInterval,
                        type: 'evening',
                    );
                }
            } else {
                $periodEnd = $this->getNextTaskDate(
                    context: $context,
                    dateParts: $data->periodEnd,
                    interval: $data->repeatInterval,
                    type: 'evening',
                );
            }
        }

        return new DateResultDTO(
            periodStart: $periodStart,
            periodEnd: $periodEnd,
        );
    }

    private function dateFromPartsForDuration(
        CommonDateContextDTO $context,
        DatePartsDTO $dateParts,
        string $type,
    ): ?CarbonImmutable {
        if ($dateParts->isEmpty()) {
            return null;
        }

        $tz = new DateTimeZone($context->timezone);
        $now = $this->dateFactory->now($tz);

        $date = $this->dateFactory->safeDate(
            year: $dateParts->year ?? $now->year,
            month: $dateParts->month ?? $now->month,
            day: $dateParts->day ?? $now->day,
            hour: 0,
            minute: 0,
            second: 0,
            timezone: $tz,
        );

        if ($dateParts->hour === null || $dateParts->minute === null) {
            [$hour, $minute] = $this->timeResolver->defaultHourMinuteForDate(
                $date,
                $context->defaultTime,
                $type,
            );
        } else {
            $hour = $dateParts->hour;
            $minute = $dateParts->minute;
        }

        return $date->setTime($hour, $minute);
    }
}
