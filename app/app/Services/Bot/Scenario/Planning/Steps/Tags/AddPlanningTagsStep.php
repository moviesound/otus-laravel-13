<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Helpers\Scenarios\Tags\TagFormatter;
use App\Services\Bot\Scenario\Planning\Steps\AddPlanningDescriptionStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddPlanningTagsStep implements StepInterface
{
    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
    )
    {
    }

    public const STEP_KEY = 'addPlanningTag';

    public static function stepKey(): string
    {
        return static::STEP_KEY
            ?? throw new \LogicException('Step KEY not defined');
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = trim($context->messageDTO->value());

        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Stop =>
            StepResultFactory::switch(PlanningDoneStep::STEP_KEY),

            MessageIntent::Back =>
            StepResultFactory::switch(AddPlanningDescriptionStep::STEP_KEY),

            MessageIntent::Continue =>
            StepResultFactory::switch(SelectRepeatingOrDateStep::STEP_KEY),

            MessageIntent::Edit =>
            StepResultFactory::switch(EditPlanningTagsStep::STEP_KEY),

            MessageIntent::Delete =>
            StepResultFactory::switch(DeletePlanningTagsStep::STEP_KEY),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(
        BotContext $context,
        string     $message
    ): StepResultDTO
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $newTags = TagFormatter::parse($message);

        $existingTags = $data['tags'] ?? [];

        if (is_string($existingTags)) {
            $existingTags = TagFormatter::parse($existingTags);
        }

        $tags = array_values(
            array_unique(
                array_merge($existingTags, $newTags)
            )
        );

        return StepResultFactory::switch(
            SelectRepeatingOrDateStep::STEP_KEY,
            [
                'tags' => $tags,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string    $error = null
    ): void
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $hasTags = !empty($data['tags'] ?? []);

        $answer = $this->buildAnswer(
            $context,
            $messenger,
            $lang,
            $error,
            $hasTags
        );

        $buttons = $this->buildButtons(
            $messenger,
            $lang,
            $hasTags
        );

        $context->messenger?->sendMessage(
            $answer,
            $buttons
        );
    }

    private function buildAnswer(
        BotContext $context,
        string     $messenger,
        string     $lang,
        ?string    $error,
        bool       $hasTags
    ): string
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $type = $data['type'];

        $headerKey = ScenarioHelper::headerKey($context);

        if ($hasTags) {
            $tags = $data['tags'];

            if (is_array($tags)) {
                $tags = TagFormatter::hash($tags);
            }

            $text = $this->textResolver->get(
                'task_change_tags',
                $messenger,
                $lang,
                [
                    'tags' => $tags,
                ]
            );
        } else {
            $textKey = $type === 'task'
                ? 'task_add_tags'
                : 'event_add_tags';

            $text = $this->textResolver->get(
                $textKey,
                $messenger,
                $lang
            );
        }

        return $context->messenger->format(
            $this->textResolver->header($messenger, $lang, $headerKey, 'task_step_tags'),
            $text,
            $error
        );
    }

    private function buildButtons(
        string $messenger,
        string $lang,
        bool   $hasTags
    ): array
    {
        $buttons = [];

        if ($hasTags) {
            $buttons[] = [
                [
                    'text' => $this->textResolver->get(
                        'edit_tags_btn',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'edit',
                ],
                [
                    'text' => $this->textResolver->get(
                        'delete_tags_emo',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'delete',
                ],
            ];
        }

        $buttons[] = MessageButtons::defaultActions(
            textResolver: $this->textResolver,
            messenger: $messenger,
            lang: $lang,
            backBtn: true,
            skipBtn: true,
            cancelBtn: true,
        );

        return $buttons;
    }
}
