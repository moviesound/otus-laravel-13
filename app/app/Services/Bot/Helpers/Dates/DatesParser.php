<?php

namespace App\Services\Bot\Helpers\Dates;

final class DatesParser
{
    public static function parseDateTimeString(
        string $input,
        ?int $defaultYear = null
    ): ?array {

        $input = self::normalize($input);

        [$input, $hour, $minute] = self::extractTime($input);

        [$input, $year] = self::extractYear(
            $input,
            $defaultYear
        );

        $date = self::parseNumericDate($input)
            ?? self::parseMonthNameDate($input);

        if ($date === null) {
            return null;
        }

        return [
            'year' => $year,
            'month' => $date['month'],
            'day' => $date['day'],
            'hour' => $hour,
            'minute' => $minute,
        ];
    }

    private static function extractYear(
        string $input,
        ?int $defaultYear
    ): array {

        if (preg_match(
            '/(\d{4})/',
            $input,
            $match
        )) {
            return [
                trim(str_replace($match[1], '', $input)),
                (int)$match[1],
            ];
        }

        return [
            trim($input),
            $defaultYear,
        ];
    }

    private static function parseNumericDate(
        string $input
    ): ?array {

        if (preg_match(
            '/(\d{1,2})[.\/](\d{1,2})/',
            $input,
            $match
        )) {
            return [
                'day' => (int)$match[1],
                'month' => (int)$match[2],
            ];
        }

        if (preg_match(
            '/(\d{1,2})-(\d{1,2})/',
            $input,
            $match
        )) {
            return [
                'month' => (int)$match[1],
                'day' => (int)$match[2],
            ];
        }

        return null;
    }

    private static function parseMonthNameDate(
        string $input
    ): ?array {

        foreach (self::getMonthMap() as $map) {

            foreach ($map as $name => $num) {

                if (!str_contains($input, $name)) {
                    continue;
                }

                if (preg_match(
                    '/(\d{1,2})\s*' . preg_quote($name, '/') . '/',
                    $input,
                    $match
                )) {
                    return [
                        'day' => (int)$match[1],
                        'month' => $num,
                    ];
                }

                if (preg_match(
                    '/' . preg_quote($name, '/') . '\s*(\d{1,2})/',
                    $input,
                    $match
                )) {
                    return [
                        'day' => (int)$match[1],
                        'month' => $num,
                    ];
                }
            }
        }

        return null;
    }
    private static function extractTime(string $input): array
    {
        $hour = null;
        $minute = null;

        if (preg_match(
            '/(\d{1,2}):(\d{1,2})/',
            $input,
            $match
        )) {
            $hour = (int)$match[1];
            $minute = (int)$match[2];

            $input = str_replace(
                $match[0],
                '',
                $input
            );
        }

        return [
            trim($input),
            $hour,
            $minute,
        ];
    }

    private static function normalize(string $input): string
    {
        $input = mb_strtolower(trim($input));

        return preg_replace(
            '/\s+/u',
            ' ',
            $input
        );
    }

    public static function getMonthMap(): array
    {
        return [
            'ru' => [
                'январь' => 1,
                'января' => 1,
                'январю' => 1,
                'январем' => 1,
                'январе' => 1,

                'февраль' => 2,
                'февраля' => 2,
                'февралю' => 2,
                'февралем' => 2,
                'феврале' => 2,

                'март' => 3,
                'марта' => 3,
                'марту' => 3,
                'мартом' => 3,
                'марте' => 3,

                'апрель' => 4,
                'апреля' => 4,
                'апрелю' => 4,
                'апрелем' => 4,
                'апреле' => 4,

                'май' => 5,
                'мая' => 5,
                'маю' => 5,
                'маем' => 5,
                'мае' => 5,

                'июнь' => 6,
                'июня' => 6,
                'июню' => 6,
                'июнем' => 6,
                'июне' => 6,

                'июль' => 7,
                'июля' => 7,
                'июлю' => 7,
                'июлем' => 7,
                'июле' => 7,

                'август' => 8,
                'августа' => 8,
                'августу' => 8,
                'августом' => 8,
                'августе' => 8,

                'сентябрь' => 9,
                'сентября' => 9,
                'сентябрю' => 9,
                'сентябрем' => 9,
                'сентябре' => 9,

                'октябрь' => 10,
                'октября' => 10,
                'октябрю' => 10,
                'октябрем' => 10,
                'октябре' => 10,

                'ноябрь' => 11,
                'ноября' => 11,
                'ноябрю' => 11,
                'ноябрем' => 11,
                'ноябре' => 11,

                'декабрь' => 12,
                'декабря' => 12,
                'декабрю' => 12,
                'декабрем' => 12,
                'декабре' => 12,
            ],

            'en' => [
                'january' => 1,
                'february' => 2,
                'march' => 3,
                'april' => 4,
                'may' => 5,
                'june' => 6,
                'july' => 7,
                'august' => 8,
                'september' => 9,
                'october' => 10,
                'november' => 11,
                'december' => 12,
            ],

            'es' => [
                'enero' => 1,
                'febrero' => 2,
                'marzo' => 3,
                'abril' => 4,
                'mayo' => 5,
                'junio' => 6,
                'julio' => 7,
                'agosto' => 8,
                'septiembre' => 9,
                'octubre' => 10,
                'noviembre' => 11,
                'diciembre' => 12,
            ],
        ];
    }
}
