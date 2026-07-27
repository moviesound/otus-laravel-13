<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating\Types;

use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Messengers\MessengerTextResolver;

final class WeeklyRepeatFormatter
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly DatesFormatter $datesFormatter,
    ) {
    }

    public function format(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        $weekDays = $data['week_days'] ?? null;

        if (!$weekDays) {
            return '';
        }

        $days = $this->parseDays($weekDays);

        return $this->textResolver->get(
            'week_days_choosen',
            $messenger,
            $lang,
            [
                'type' => $this->resolveType($data, $messenger, $lang),
                'days' => $this->buildDaysString($days, $data, $messenger, $lang),
            ],
        );
    }

    private function parseDays(string $weekDays): array
    {
        $arr = array_map('trim', explode(',', $weekDays));
        $arr = array_filter($arr, fn($v) => $v !== '');
        return array_values($arr);
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

            $result .= $this->datesFormatter->weekdayName(
                (int)$day,
                $messenger,
                $lang,
                false,
                false,
                true
            );

            $result .= $this->buildTimeSuffix($day, $data);
        }

        return $result;
    }

    private function buildTimeSuffix(string $day, array $data): string
    {
        $common = $data['weekly_common_time'] ?? null;
        $diff = $data['weekly_different_time'] ?? null;

        if (is_array($common)) {
            return ' (' . $common['time_start']
                . (!empty($common['time_end']) ? '-' . $common['time_end'] : '')
                . ')';
        }

        if (is_array($diff) && isset($diff[$day])) {
            $t = $diff[$day];

            return ' (' . $t['time_start']
                . (!empty($t['time_end']) ? '-' . $t['time_end'] : '')
                . ')';
        }

        return '';
    }

    private function resolveType(array $data, string $messenger, string $lang): string
    {
        $type = $data['type'] ?? 'task';

        return $this->textResolver->get(
            $type . '_rod',
            $messenger,
            $lang,
        );
    }
}
