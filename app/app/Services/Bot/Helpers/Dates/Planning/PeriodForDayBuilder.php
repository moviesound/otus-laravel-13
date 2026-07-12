<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\DTO\Bot\Scenarios\Planning\DateResultDTO;
use App\DTO\Bot\Scenarios\Planning\TimePeriodDTO;
use App\DTO\Bot\User\UserDefaultTimeDTO;
use Carbon\CarbonImmutable;

final readonly class PeriodForDayBuilder
{
    public function __construct(
        private TimeResolver $timeResolver,
    )
    {
    }

    /**
     * Если есть common time:
     * - time_start + time_end => period
     * - только time_start => deadline
     *
     * Если common нет, но есть rule — аналогично.
     *
     * Если нет ничего — period на defaultTime.
     */
    public function build(
        CarbonImmutable    $baseDateStart,
        CarbonImmutable    $baseDateEnd,
        ?TimePeriodDTO     $commonTime,
        ?TimePeriodDTO     $ruleTime,
        UserDefaultTimeDTO $defaultTime,
    ): DateResultDTO
    {
        $time = null;

        if ($commonTime !== null && !$commonTime->isEmpty()) {
            $time = $commonTime;
        } elseif ($ruleTime !== null && !$ruleTime->isEmpty()) {
            $time = $ruleTime;
        }

        if ($time !== null) {
            [$hourStart, $minuteStart] = $this->timeResolver->parseTime(
                $time->timeStart,
                '00:00',
            );

            if (!empty($time->timeEnd)) {
                [$hourEnd, $minuteEnd] = $this->timeResolver->parseTime(
                    $time->timeEnd,
                    '21:00',
                );

                return new DateResultDTO(
                    periodStart: $baseDateStart->setTime($hourStart, $minuteStart),
                    periodEnd: $baseDateEnd->setTime($hourEnd, $minuteEnd),
                );
            }

            return new DateResultDTO(
                deadline: $baseDateStart->setTime($hourStart, $minuteStart),
            );
        }

        [$hourStart, $minuteStart] = $this->timeResolver->parseTime(
            $this->timeResolver->getTimeByDate($baseDateStart, $defaultTime, 'start'),
            '08:00',
        );

        [$hourEnd, $minuteEnd] = $this->timeResolver->parseTime(
            $this->timeResolver->getTimeByDate($baseDateEnd, $defaultTime, 'end'),
            '21:00',
        );

        return new DateResultDTO(
            periodStart: $baseDateStart->setTime($hourStart, $minuteStart),
            periodEnd: $baseDateEnd->setTime($hourEnd, $minuteEnd),
        );
    }
}
