<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating\Types;

use App\Services\Bot\Messengers\MessengerTextResolver;

final class QuarterlyRepeatFormatter
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
        $quarterType = $data['quarter_type'] ?? null;

        return match ($quarterType) {

            'deadline' => $this->deadline($data, $messenger, $lang),

            'period' => $this->period($data, $messenger, $lang),

            default => '',
        };
    }

    private function deadline(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        return $this->text(
            $messenger,
            $lang,
            $this->getAlias($messenger, 'quarterly_deadline_chosen'),
            [
                'type' => $this->resolveType($data, $messenger, $lang),
                'month' => $data['month_in_quarter'] ?? null,
                'day' => $data['day_in_quarter'] ?? null,
            ],
        );
    }

    private function period(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        return $this->text(
            $messenger,
            $lang,
            $this->getAlias($messenger, 'quarterly_period_chosen'),
            [
                'type' => $this->resolveType($data, $messenger, $lang),
                'start_month' => $data['start_month_in_quarter'] ?? null,
                'start_day' => $data['start_day_in_quarter'] ?? null,
                'end_month' => $data['end_month_in_quarter'] ?? null,
                'end_day' => $data['end_day_in_quarter'] ?? null,
            ],
        );
    }

    private function text(
        string $messenger,
        string $lang,
        string $alias,
        array $params,
    ): string {
        return $this->textResolver->get(
            $alias,
            $messenger,
            $lang,
            $params,
        );
    }

    private function getAlias(
        string $messenger,
        string $base,
    ): string {
        return $messenger === 'telegram'
            ? 'telegram_' . $base
            : $base;
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
