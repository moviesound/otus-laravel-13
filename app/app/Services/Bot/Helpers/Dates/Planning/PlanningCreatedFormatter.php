<?php

namespace App\Services\Bot\Helpers\Dates\Planning;

use App\Services\Bot\Helpers\Scenarios\Summeries\PlanningPreviewFormatter;
use App\Services\Bot\Messengers\MessengerTextResolver;

final readonly class PlanningCreatedFormatter
{
    public function __construct(
        private PlanningPreviewFormatter $previewFormatter,
        private MessengerTextResolver $textResolver,
    ) {
    }

    public function format(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        $header = $this->header($data, $messenger, $lang);

        $preview = $this->previewFormatter->format(
            $data,
            $messenger,
            $lang,
        );

        return trim($header . "\n\n" . $preview);
    }

    private function header(
        array $data,
        string $messenger,
        string $lang,
    ): string {
        if ($messenger === 'telegram') {
            $key = $data['type'] === 'event'
                ? 'telegram_event_was_created'
                : 'telegram_task_was_created';

            $text = $this->textResolver->get($key, $messenger, $lang);

            if ($text !== $key && $text !== '') {
                return $text;
            }
        }

        $key = $data['type'] === 'event'
            ? 'event_was_created'
            : 'task_was_created';

        $text = $this->textResolver->get($key, $messenger, $lang);

        if ($text !== $key && $text !== '') {
            return $text;
        }

        return $data['type'] === 'event'
            ? 'Событие создано'
            : 'Задача создана';
    }
}
