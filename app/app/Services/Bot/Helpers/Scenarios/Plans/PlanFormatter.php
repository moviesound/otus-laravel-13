<?php

namespace App\Services\Bot\Helpers\Scenarios\Plans;

use App\Services\Bot\Helpers\Dates\UserDateTimeFormatter;

final class PlanFormatter
{
    public static function nums(
        array $plans,
        string $timezone,
        int $offset = 0
    ): string {
        $lines = [];
        $number = $offset;

        foreach ($plans as $plan) {
            $number++;

            $title = $plan->title ?? '';

            $row = $number . '. ' . $title;

            if (!empty($plan->nearest_time)) {
                $row .= ' ⇒ ' . UserDateTimeFormatter::utcToUserLocal(
                        $plan->nearest_time,
                        $timezone,
                        'short'
                    );
            }

            $lines[] = mb_substr(
                $row,
                0,
                100,
                'UTF-8'
            );
        }

        return implode(
            "\n",
            $lines
        );
    }
}
