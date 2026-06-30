<?php

namespace App\Services\Bot\Helpers\Dates;

use App\Services\Bot\Messengers\MessengerTextResolver;

final readonly class DatesFormatter
{
    public function __construct(
        private MessengerTextResolver $textResolver,
    )
    {
    }

    public function human(
        array  $date,
        string $messenger,
        string $lang,
    ): string
    {
        $result = [];

        if (!empty($date['day'])) {
            $result[] = $date['day'];
        }

        if (!empty($date['month'])) {
            $result[] = $this->monthName(
                number: (int)$date['month'],
                messenger: $messenger,
                lang: $lang
            );
        }

        if (!empty($date['year'])) {
            $result[] = $date['year'];
        }

        if (
            isset($date['hour']) &&
            isset($date['minute'])
        ) {
            $result[] = sprintf(
                '%02d:%02d',
                $date['hour'],
                $date['minute']
            );
        }

        return implode(' ', $result);
    }

    public function monthName(
        int    $number,
        string $messenger,
        string $lang,
        bool   $short = false,
        bool   $rod = true,
    ): string
    {

        $aliases = [
            1 => 'january',
            2 => 'february',
            3 => 'march',
            4 => 'april',
            5 => 'may',
            6 => 'june',
            7 => 'july',
            8 => 'august',
            9 => 'september',
            10 => 'october',
            11 => 'november',
            12 => 'december',
        ];

        $alias = $aliases[$number] ?? null;

        if (!$alias) {
            return '';
        }

        if ($short) {
            $alias .= '_short';
        } elseif ($rod) {
            $alias .= '_rod';
        }

        return $this->textResolver->get(
            $alias,
            $messenger,
            $lang
        );
    }

    public function weekdayName(
        int $number,
        string $messenger,
        string $lang,
        bool $short = false,
        bool $plural = false,
        bool $rod = false,
    ): string
    {
        $aliases = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        $alias = $aliases[$number] ?? null;

        if (!$alias) {
            return '';
        }

        if ($short) {
            $alias .= '_short';
        } elseif ($plural) {
            $alias .= '_plural';

            if ($rod) {
                $alias .= '_rod';
            }
        } elseif ($rod) {
            $alias .= '_rod';
        }

        return $this->textResolver->get(
            $alias,
            $messenger,
            $lang,
        );
    }
}
