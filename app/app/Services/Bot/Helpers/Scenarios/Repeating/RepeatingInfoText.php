<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating;

use App\Services\Bot\Helpers\Scenarios\Repeating\Types\DailyRepeatFormatter;
use App\Services\Bot\Helpers\Scenarios\Repeating\Types\MonthlyRepeatFormatter;
use App\Services\Bot\Helpers\Scenarios\Repeating\Types\NoRepeatDateFormatter;
use App\Services\Bot\Helpers\Scenarios\Repeating\Types\QuarterlyRepeatFormatter;
use App\Services\Bot\Helpers\Scenarios\Repeating\Types\WeeklyRepeatFormatter;
use App\Services\Bot\Helpers\Scenarios\Repeating\Types\YearlyRepeatFormatter;
use App\Services\Bot\Messengers\MessengerTextResolver;

final class RepeatingInfoText
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly NoRepeatDateFormatter $noRepeatDateFormatter,
        private readonly DailyRepeatFormatter $dailyRepeatFormatter,
        private readonly WeeklyRepeatFormatter $weeklyRepeatFormatter,
        private readonly MonthlyRepeatFormatter $monthlyRepeatFormatter,
        private readonly QuarterlyRepeatFormatter $quarterlyRepeatFormatter,
        private readonly YearlyRepeatFormatter $yearlyRepeatFormatter,
    ) {
    }

    public function build(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        $title = $this->textResolver->get(
            'current_choose',
            $messenger,
            $lang,
        );

        return match ($data['repeat_type'] ?? null) {

            'none' => $this->buildNoRepeat(
                $title,
                $data,
                $messenger,
                $lang,
            ),

            'daily' => $title . $this->dailyRepeatFormatter->format(
                    $data,
                    $messenger,
                    $lang,
                ),

            'weekly' => $title . $this->weeklyRepeatFormatter->format(
                    $data,
                    $messenger,
                    $lang,
                ),

            'monthly' => $title . $this->monthlyRepeatFormatter->format(
                    $data,
                    $messenger,
                    $lang,
                ),

            'quarterly' => $title . $this->quarterlyRepeatFormatter->format(
                    $data,
                    $messenger,
                    $lang,
                ),

            'yearly' => $title . $this->yearlyRepeatFormatter->format(
                    $data,
                    $messenger,
                    $lang,
                ),

            default => '',
        };
    }

    private function buildNoRepeat(
        string $title,
        array $data,
        string $messenger,
        string $lang,
    ): string {
        if (
            !isset($data['date_mode'])
            && (
                !isset($data['deadline_date'])
                || !isset($data['period_start'], $data['period_end'])
            )
        ) {
            return '';
        }

        return $title . $this->noRepeatDateFormatter->format(
                messenger: $messenger,
                type: $data['type'] ?? 'task',
                lang: $lang,
                mode: $data['date_mode'] ?? 'deadline',
                deadline: $data['deadline_date'] ?? null,
                periodStart: $data['period_start'] ?? null,
                periodEnd: $data['period_end'] ?? null,
            );
    }
}
