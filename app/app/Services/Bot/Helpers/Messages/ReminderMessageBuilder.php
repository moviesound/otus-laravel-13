<?php

namespace App\Services\Bot\Helpers\Messages;

use App\Services\Bot\Contexts\ReminderContext;
use App\Services\Bot\Helpers\Scenarios\Tags\TagFormatter;

class ReminderMessageBuilder
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
    ) {}

    public function build(ReminderContext $context): ?string
    {
        $messenger = $context->user
            ->mainSocial()
            ?->type;

        if ($messenger) {
            $lang = $context->user->language;

            $key = match ($context->reminderTemplate->entity_type) {
                'task' => 'task_remind',
                'event' => 'event_remind',
            };

            $description = $this->description($context, $messenger, $lang);

            $list = $this->list($context, $messenger, $lang);

            return $this->textResolver->get(
                $key,
                $messenger,
                $lang,
                [
                    'title' => $context->entityTemplate->title,
                    'description' => $description,
                    'remind' => $context->reminderTemplate->text,
                    'list' => $list,
                    'tags' => $this->tags($context),
                ]
            );
        }

        return null;
    }

    private function description(ReminderContext $context, string $messenger, string $lang): ?string
    {
        if (!$context->entityTemplate->description) {
            return null;
        }

        return $this->textResolver->get(
                'description',
                $messenger,
                $lang,) . $context->entityTemplate->description;
    }

    private function list(ReminderContext $context, string $messenger, string $lang): ?string
    {
        //TODO: after creating list scenario
        return null;
    }


    private function tags(ReminderContext $context): ?string
    {
        if (!$context->tags) {
            return null;
        }

        return TagFormatter::hash($context->tags);
    }
}
