<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongTagNumberMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Helpers\Scenarios\Tags\TagFormatter;
use App\Services\Bot\Scenario\StepResultFactory;

final class EditPlanningTagsStep implements StepInterface
{
    public const STEP_KEY = 'editPlanningTags';

    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongTagNumberMessage $wrongNumberMessage,
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
                AddPlanningTagsStep::STEP_KEY
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
        string $message
    ): StepResultDTO {
        if (!ctype_digit($message)) {
            return StepResultFactory::repeat(
                $this->wrongNumberMessage->get($context)
            );
        }

        $index = (int)$message - 1;
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $tags = $data['tags'] ?? [];

        if (!isset($tags[$index])) {
            return StepResultFactory::repeat(
                $this->wrongNumberMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            EditPlanningTagValueStep::STEP_KEY,
            [
                'editingTagIndex' => $index,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {

        [$messenger, $lang] =
            MessageContext::getMessengerAndLang($context);
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $tags = $data['tags'] ?? [];

        $answer = $this->buildAnswer(
            $context,
            $messenger,
            $lang,
            $tags,
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
        string $messenger,
        string $lang,
        array $tags,
        ?string $error
    ): string {
        $headerKey = ScenarioHelper::headerKey($context);

        $tagsList = TagFormatter::nums(
            $tags
        );

        $text =  $this->textResolver->get(
            'edit_tags',
            $messenger,
            $lang,
            [
                'tags' => $tagsList,
            ]
        );

        return $context->messenger->format(
            $this->textResolver->header($messenger, $lang, $headerKey, 'task_step_tags'),
            $text,
            $error
        );
    }
}
