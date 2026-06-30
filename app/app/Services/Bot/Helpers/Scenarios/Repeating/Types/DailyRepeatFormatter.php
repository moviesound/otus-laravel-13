<?php

namespace App\Services\Bot\Helpers\Scenarios\Repeating\Types;

use App\Services\Bot\Messengers\MessengerTextResolver;

final class DailyRepeatFormatter
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
        $interval = (int)($data['repeat_interval'] ?? 1);

        return $interval === 1
            ? $this->single($data, $messenger, $lang)
            : $this->multiple($data, $messenger, $lang, $interval);
    }

    private function single(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        return $this->textResolver->get(
            'daily_selected',
            $messenger,
            $lang,
            [
                'type' => $this->resolveType($data, $messenger, $lang),
            ],
        );
    }

    private function multiple(
        array $data,
        string $messenger,
        string $lang,
        int $interval,
    ): string {
        return $this->textResolver->get(
            'daily_n_selected',
            $messenger,
            $lang,
            [
                'type' => $this->resolveTypePlural($data, $messenger, $lang),
                'days' => $interval - 1,
            ],
        );
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

    private function resolveTypePlural(array $data, string $messenger, string $lang): string
    {
        $type = $data['type'] ?? 'task';

        return $this->textResolver->get(
            $type . '_pl_tv',
            $messenger,
            $lang,
        );
    }
}
