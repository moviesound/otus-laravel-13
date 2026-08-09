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
use App\Services\Bot\Scenario\StepResultFactory;

final class EditPlanningTagValueStep implements StepInterface
{
    public const STEP_KEY = 'editPlanningTagValue';

    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
    )
    {
    }

    public static function stepKey(): string
    {
        return static::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = trim($context->messageDTO->value());

        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Back =>
            StepResultFactory::switch(
                EditPlanningTagsStep::STEP_KEY
            ),

            default =>
            $this->handleMessage(
                $context,
                $message
            ),
        };
    }

    private function handleMessage(
        BotContext $context,
        string     $message
    ): StepResultDTO
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $index = $data['editingTagIndex'] ?? null;

        $tags = $data['tags'] ?? [];

        if (
            $index === null ||
            !isset($tags[$index])
        ) {
            return StepResultFactory::switch(
                EditPlanningTagsStep::STEP_KEY
            );
        }

        $tags[(int)$index] = trim($message);

        return StepResultFactory::switch(
            AddPlanningTagsStep::STEP_KEY,
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
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $index = $data['editingTagIndex'] ?? null;

        $tags = $data['tags'] ?? [];

        $tag = isset($tags[$index])
            ? $tags[$index]
            : '';

        $answer = $this->buildAnswer(
            $context,
            $messenger,
            $lang,
            $tag,
            $error
        );

        $buttons = [
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true
            )
        ];

        $context->messenger?->sendMessage(
            $answer,
            $buttons
        );
    }

    private function buildAnswer(
        BotContext $context,
        string     $messenger,
        string     $lang,
        string     $tag,
        ?string    $error
    ): string
    {
        $headerKey = ScenarioHelper::headerKey($context);

        $text = $this->textResolver->get(
            'enter_edit_tag',
            $messenger,
            $lang,
            [
                'tag' => $tag,
            ]
        );

        return $context->messenger->format(
            $this->textResolver->header($messenger, $lang, $headerKey, 'task_step_tags'),
            $text,
            $error
        );
    }
}
