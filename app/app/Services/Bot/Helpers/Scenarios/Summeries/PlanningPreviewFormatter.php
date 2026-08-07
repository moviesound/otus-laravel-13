<?php

namespace App\Services\Bot\Helpers\Scenarios\Summeries;

use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Reminders\ReminderFormatter;
use App\Services\Bot\Helpers\Scenarios\Repeating\RepeatingInfoText;
use App\Services\Bot\Helpers\Scenarios\Tags\TagFormatter;

final readonly class PlanningPreviewFormatter
{
    public function __construct(
        private MessengerTextResolver $textResolver,
        private ReminderFormatter     $reminderFormatter,
        private RepeatingInfoText     $repeatingInfoText,
        private DatesFormatter         $datesFormatter,
    )
    {
    }

    public function format(
        array  $data,
        string $messenger,
        string $lang,
        bool   $done = false
    ): string
    {

        $description = $this->description(
            $data,
            $messenger,
            $lang,
        );

        $tags = $this->tags(
            $data,
            $messenger,
            $lang,
        );

        $repeat = $this->repeat(
            $data,
            $messenger,
            $lang,
        );

        $reminders = $this->reminders(
            $data,
            $messenger,
            $lang,
        );

        $deadline = ''; /*$this->deadline(
            $data,
            $messenger,
            $lang,
        );*/

        if ($done === false) {
            $label = 'task_was_created_ask';
            $type = $this->textResolver->get(
                $data['type'] . ($messenger === 'telegram' ? '_dat' : '_vin'),
                $messenger,
                $lang,
            );
        } else {
            $label = 'task_was_created';
            $type = $this->textResolver->get(
                $data['type'] . ($messenger === 'telegram' ? '' : '_vin'),
                $messenger,
                $lang,
            );
        }

        return $this->textResolver->get(
            $label,
            $messenger,
            $lang,
            [
                'type' => $type,

                'subtype' => $this->textResolver->get(
                    $data['subType'] . '_emo',
                    $messenger,
                    $lang,
                ),

                'title' => $data['title'] ?? '',

                'description' => $description,

                'tags' => $tags,

                'repeat' => $repeat,

                'reminders' => $reminders,

                'it' => $this->textResolver->get(
                    ($data['type'] ?? 'task') === 'task'
                        ? 'it_her'
                        : 'it_his',
                    $messenger,
                    $lang,
                ),

                'deadline' => $deadline,
            ]
        );
    }

    private function deadline(
        array  $data,
        string $messenger,
        string $lang,
    )
    {
        $deadline = '';
        if (isset($data['date_mode']) && !empty($data['date_mode'])) {
            $periodStart = ($data['period_start']['day'] ?? '') . ' ' .
                $this->datesFormatter->monthName(
                    number: $data['period_start']['month'] ?? 0,
                    messenger: $messenger,
                    lang: $lang
                ) .
                (isset($data['period_start']['year']) ? ' ' . $data['period_start']['year'] : '') .
                (isset($data['period_start']['hour']) ? ' ' . (
                    $data['period_start']['hour'] > 9 ? $data['period_start']['hour'] : '0' . $data['period_start']['hour']) : '') .
                (isset($data['period_start']['minute']) ? ':' . (
                    $data['period_start']['minute'] > 9 ? $data['period_start']['minute'] : '0' . $data['period_start']['minute']) : '');
            $periodEnd = isset($data['period_end']) ? ' - ' . ($data['period_end']['day'] ?? '') . ' ' .
                $this->datesFormatter->monthName(
                    number: $data['period_end']['month'] ?? 0,
                    messenger: $messenger,
                    lang: $lang
                ) .
                (isset($data['period_end']['year']) ? ' ' . $data['period_end']['year'] : '') .
                (isset($data['period_end']['hour']) ? ' ' . (
                    $data['period_end']['hour'] > 9 ? $data['period_end']['hour'] : '0' . $data['period_end']['hour']) : '') .
                (isset($data['period_end']['minute']) ? ':' . (
                    $data['period_end']['minute'] > 9 ? $data['period_end']['minute'] : '0' . $data['period_end']['minute']) : '') : '';
            $deadlineDate = ($data['deadline_date']['day'] ?? '') . ' ' .
                $this->datesFormatter->monthName(
                    number: $data['deadline_date']['month'] ?? 0,
                    messenger: $messenger,
                    lang: $lang
                ) .
                (isset($data['deadline_date']['year']) ? ' ' . $data['deadline_date']['year'] : '') .
                (isset($data['deadline_date']['hour']) ? ' ' . (
                    $data['deadline_date']['hour'] > 9 ? $data['deadline_date']['hour'] : '0' . $data['deadline_date']['hour']) : '') .
                (isset($data['deadline_date']['minute']) ? ':' . (
                    $data['deadline_date']['minute'] > 9 ? $data['deadline_date']['minute'] : '0' . $data['deadline_date']['minute']) : '');
            $deadline = isset($data['date_mode']) && $data['date_mode'] === 'period' ? $periodStart . $periodEnd
                : $deadlineDate ?? null;
        }
        return isset($deadline) && !empty($deadline) ?
            "\n" .
            (isset($repeat) && !empty($repeat) ?
                $this->textResolver->get(
                    'nearest_date',
                    $messenger,
                    $lang)
                : $this->textResolver->get(
                    'date',
                    $messenger,
                    $lang)
            ) . ': ' . $deadline . "\n" : '';
    }

    private function description(
        array  $data,
        string $messenger,
        string $lang,
    ): string
    {

        if (empty($data['description'])) {
            return '';
        }

        $label = $this->textResolver->get(
            'description',
            $messenger,
            $lang,
        );

        if ($messenger === 'telegram') {
            return "\n<i>{$label}:</i>\n{$data['description']}\n";
        }

        return "\n{$label}:\n{$data['description']}\n";
    }

    private function tags(
        array  $data,
        string $messenger,
        string $lang,
    ): string
    {

        if (empty($data['tags'])) {
            return '';
        }

        $label = $this->textResolver->get(
            'tags',
            $messenger,
            $lang,
        );

        $tags = TagFormatter::hash(
            is_array($data['tags'])
                ? $data['tags']
                : [$data['tags']]
        );

        if ($messenger === 'telegram') {
            return "\n<i>{$label}:</i> {$tags}\n";
        }

        return "\n{$label}: {$tags}\n";
    }

    private function repeat(
        array  $data,
        string $messenger,
        string $lang,
    ): string
    {

        if (empty($data['repeat_type'])) {
            return '';
        }

        return "\n" .
            $this->repeatingInfoText->build(
                $data,
                $messenger,
                $lang,
            ) .
            "\n";
    }

    private function reminders(
        array  $data,
        string $messenger,
        string $lang,
    ): string
    {

        if (empty($data['reminders'])) {
            return '';
        }

        return "\n" .
            $this->reminderFormatter->list(
                $data['reminders'],
                $messenger,
                $lang,
            ) .
            "\n";
    }
}
