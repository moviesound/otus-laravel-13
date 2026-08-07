<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating\Types;

use App\Services\Bot\Helpers\Messages\MessengerTextResolver;

final class MonthlyRepeatFormatter
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
    ) {
    }

    public function format(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        $monthDays = $data['month_days'] ?? null;

        if (!$monthDays) {
            return '';
        }

        $days = $this->parseDays($monthDays);

        $daysString = $this->buildDaysString(
            $days,
            $data,
            $messenger,
            $lang,
        );

        return $this->textResolver->get(
            'month_days_choosen',
            $messenger,
            $lang,
            [
                'type' => $this->resolveType($data, $messenger, $lang),
                'days' => $daysString,
            ],
        );
    }

    private function parseDays(string $monthDays): array
    {
        return array_map('trim', explode(',', $monthDays));
    }

    private function buildDaysString(
        array $days,
        array $data,
        string $messenger,
        string $lang,
    ): string {
        $result = '';

        foreach ($days as $day) {
            $result .= ($result === '' ? '' : ', ');
            $result .= $day;

            $result .= $this->buildTimeSuffix(
                $day,
                $data,
            );
        }

        return $result;
    }

    private function buildTimeSuffix(
        string $day,
        array $data,
    ): string {
        $common = $data['monthly_common_time'] ?? null;
        $different = $data['monthly_different_time'] ?? null;

        if (is_array($common)) {
            return ' (' . $common['time_start']
                . ($common['time_end'] ? '-' . $common['time_end'] : '')
                . ')';
        }

        if (is_array($different)) {
            $day = trim($day);

            return ' (' . $different[$day]['time_start']
                . ($different[$day]['time_end']
                    ? '-' . $different[$day]['time_end']
                    : ''
                )
                . ')';
        }

        return '';
    }

    private function resolveType(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        $type = $data['type'] ?? 'task';

        return $this->textResolver->get(
            $type . '_rod',
            $messenger,
            $lang,
        );
    }
}
