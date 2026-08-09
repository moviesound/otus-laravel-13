<?php

namespace App\Services\Bot\Helpers\Dates;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use DateTimeZone;

final class UserDateTimeFormatter
{
    public static function utcToUserLocal(
        string $dateTime,
        string $timezone,
        string $type = 'iso',
        bool $showTime = true,
        string $lang = 'ru'
    ): string {
        try {
            $dt = CarbonImmutable::parse(
                $dateTime,
                'UTC'
            )->setTimezone(
                new DateTimeZone($timezone)
            );
        } catch (\Throwable) {
            return '';
        }

        if ($type === 'iso') {
            return $dt->format(
                'Y-m-d H:i:s'
            );
        }

        if ($type === 'text') {
            $day = $dt->day;
            $monthNum = $dt->month;

            $months = DatesParser::getMonthMap()[$lang]
                ?? DatesParser::getMonthMap()['ru'];

            /**
             * getMonthMap имеет вид:
             * 'января' => 1
             *
             * переворачиваем:
             * 1 => января
             */
            $monthMap = array_flip($months);

            $monthName = $monthMap[$monthNum]
                ?? $dt->format('F');

            $result = $day . ' ' . $monthName;

            if ($showTime) {
                $result .= ', ' . $dt->format('H:i');
            }

            return $result;
        }

        return $dt->format(
            'd.m.Y H:i'
        );
    }
}
