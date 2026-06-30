<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating\Types;

use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Messengers\MessengerTextResolver;

final class NoRepeatDateFormatter
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly DatesFormatter $datesFormatter,
    ) {
    }

    public function format(
        string $messenger,
        string $type,
        string $lang,
        string $mode,
        ?array $deadline,
        ?array $periodStart,
        ?array $periodEnd,
    ): string {
        return $type === 'task'
            ? $this->formatTask(
                $messenger,
                $lang,
                $mode,
                $deadline,
                $periodStart,
                $periodEnd,
            )
            : $this->formatEvent(
                $messenger,
                $lang,
                $mode,
                $deadline,
                $periodStart,
                $periodEnd,
            );
    }

    private function formatTask(
        string $messenger,
        string $lang,
        string $mode,
        ?array $deadline,
        ?array $periodStart,
        ?array $periodEnd,
    ): string {
        if ($mode === 'deadline') {
            return $this->build(
                $messenger,
                $lang,
                'repeat_type_new_task',
                'one_time_task',
                $this->taskDeadlineStart($messenger, $lang),
                $this->taskDeadlineEnd($deadline, $messenger, $lang),
            );
        }

        return $this->build(
            $messenger,
            $lang,
            'repeat_type_new_task',
            'one_time_task',
            $this->taskPeriodStart($periodStart, $messenger, $lang),
            $this->taskPeriodEnd($periodEnd, $messenger, $lang),
        );
    }

    private function formatEvent(
        string $messenger,
        string $lang,
        string $mode,
        ?array $deadline,
        ?array $periodStart,
        ?array $periodEnd,
    ): string {
        if ($mode === 'deadline') {
            return $this->build(
                $messenger,
                $lang,
                'repeat_type_new_event',
                'one_time_event',
                $this->eventDeadlineStart($deadline, $messenger, $lang),
                '',
            );
        }

        return $this->build(
            $messenger,
            $lang,
            'repeat_type_new_event',
            'one_time_event',
            $this->eventPeriodStart($periodStart, $messenger, $lang),
            "\n" . $this->textResolver->get(
                'repeat_type_new_event_end',
                $messenger,
                $lang,
                [
                    'date' => $this->eventPeriodEnd(
                        $periodEnd,
                        $messenger,
                        $lang,
                    ),
                ],
            ),
        );
    }

    private function taskDeadlineStart(
        string $messenger,
        string $lang,
    ): string {
        return '1 ' . $this->datesFormatter->monthName(
                number: 1,
                messenger: $messenger,
                lang: $lang,
            );
    }

    private function taskDeadlineEnd(
        ?array $deadline,
        string $messenger,
        string $lang,
    ): string {
        return $this->formatDate(
            date: $deadline,
            messenger: $messenger,
            lang: $lang,
            showTime: true,
        );
    }

    private function taskPeriodStart(
        ?array $periodStart,
        string $messenger,
        string $lang,
    ): string {
        return $this->formatDate(
            date: $periodStart,
            messenger: $messenger,
            lang: $lang,
            showTime: true,
            alwaysShowYear: true,
        );
    }

    private function taskPeriodEnd(
        ?array $periodEnd,
        string $messenger,
        string $lang,
    ): string {
        return $this->formatDate(
            date: $periodEnd,
            messenger: $messenger,
            lang: $lang,
            showTime: true,
        );
    }

    private function eventDeadlineStart(
        ?array $deadline,
        string $messenger,
        string $lang,
    ): string {
        return $this->formatDate(
            date: $deadline,
            messenger: $messenger,
            lang: $lang,
            showTime: true,
            useAtTime: true,
        );
    }

    private function eventPeriodStart(
        ?array $periodStart,
        string $messenger,
        string $lang,
    ): string {
        return $this->formatDate(
            date: $periodStart,
            messenger: $messenger,
            lang: $lang,
            showTime: true,
            useAtTime: true,
        );
    }

    private function eventPeriodEnd(
        ?array $periodEnd,
        string $messenger,
        string $lang,
    ): string {
        return $this->formatDate(
            date: $periodEnd,
            messenger: $messenger,
            lang: $lang,
            showTime: true,
            useAtTime: true,
        );
    }

    private function build(
        string $messenger,
        string $lang,
        string $template,
        string $typeAlias,
        string $date1,
        string $date2,
    ): string {
        return $this->textResolver->get(
            $template,
            $messenger,
            $lang,
            [
                'type' => $this->textResolver->get(
                    $typeAlias,
                    $messenger,
                    $lang,
                ),
                'date1' => $date1,
                'date2' => $date2,
            ],
        );
    }

    private function formatDate(
        ?array $date,
        string $messenger,
        string $lang,
        bool $showTime = false,
        bool $useAtTime = false,
        bool $alwaysShowYear = false,
    ): string {
        if ($date === null) {
            return '';
        }

        $result = '';

        if (
            $showTime &&
            isset($date['hour'], $date['minute'])
        ) {
            if ($useAtTime) {
                $result .= $this->textResolver->get(
                        'at_time',
                        $messenger,
                        $lang,
                    ) . ' NoRepeatDateFormatter.php';
            }

            $result .= sprintf(
                    '%02d:%02d',
                    $date['hour'],
                    $date['minute'],
                ) . ', ';
        }

        $result .= $date['day'];
        $result .= ' ';
        $result .= $this->datesFormatter->monthName(
            number: $date['month'],
            messenger: $messenger,
            lang: $lang,
        );

        if (
            $alwaysShowYear ||
            $date['year'] != date('Y')
        ) {
            $result .= ' ';
            $result .= $date['year'];
            $result .= ' ';
            $result .= $this->textResolver->get(
                'year_rod',
                $messenger,
                $lang,
            );
        }

        return $result;
    }
}
