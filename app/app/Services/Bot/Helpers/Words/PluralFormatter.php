<?php

namespace App\Services\Bot\Helpers\Words;

use App\Services\Bot\Helpers\Messages\MessengerTextResolver;

final class PluralFormatter
{
    public static function word(
        MessengerTextResolver $textResolver,
        int $number,
        string $unit,
        string $messenger,
        string $lang,
    ): string
    {
        if ($lang === 'ru') {
            $n = abs($number) % 100;
            $n1 = $n % 10;

            $suffix = match (true) {
                $n > 10 && $n < 20 => '_5',
                $n1 === 1 => '_1',
                $n1 >= 2 && $n1 <= 4 => '_2',
                default => '_5',
            };

            return $textResolver->get(
                $unit . $suffix,
                $messenger,
                $lang,
            );
        }

        return $unit;
    }
}
