<?php

namespace App\Services\Bot\Helpers\Messages;

use App\Services\Bot\Contexts\ReminderContext;

class ReminderButtonsBuilder
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
    ) {}

    public function build(ReminderContext $context): array
    {
        $social = $context->user->mainSocial();

        $messenger = $social->type;
        $lang = $context->user->language;

        return match ($context->reminderTemplate->entity_type) {
            'task' => $this->taskButtons($context, $messenger, $lang),
            'event' => $this->eventButtons($context, $messenger, $lang),
        };
    }

    private function taskButtons(
        ReminderContext $context,
        string $messenger,
        string $lang,
    ): array {
        $queueId = $context->queue->id;

        return [
            [
                [
                    'text' => $this->textResolver->get(
                        'btn_is_read',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'is_read_' . $queueId,
                ],
                [
                    'text' => $this->textResolver->get(
                        'btn_task_is_done',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'task_is_done_' . $queueId,
                ],
            ],
            [
                [
                    'text' => $this->textResolver->get(
                        'btn_remind_later',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'remind_later_' . $queueId,
                ],
            ],
            [
                [
                    'text' => $this->textResolver->get(
                        'btn_cancel_task',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'cancel_task_' . $queueId,
                ],
            ],
        ];
    }

    private function eventButtons(
        ReminderContext $context,
        string $messenger,
        string $lang,
    ): array {
        $queueId = $context->queue->id;

        return [
            [
                [
                    'text' => $this->textResolver->get(
                        'btn_is_read',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'is_read_' . $queueId,
                ],
                [
                    'text' => $this->textResolver->get(
                        'btn_event_is_over',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'event_is_over_' . $queueId,
                ],
            ],
            [
                [
                    'text' => $this->textResolver->get(
                        'btn_remind_later',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'remind_later_' . $queueId,
                ],
            ],
            [
                [
                    'text' => $this->textResolver->get(
                        'btn_cancel_event',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'cancel_event_' . $queueId,
                ],
            ],
        ];
    }
}
