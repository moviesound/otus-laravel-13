<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Tags;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongNumberMessage;

use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Helpers\Scenarios\Tags\TagFormatter;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\StepResultFactory;

final class DeletePlanningTagsStep implements StepInterface
{
    public const STEP_KEY = 'deletePlanningTags';

    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongNumberMessage $wrongNumberMessage,
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

        unset($tags[$index]);

        if (empty($tags)) {
            return StepResultFactory::switch(
                AddPlanningTagsStep::STEP_KEY,
                [
                    'tags' => [],
                ]
            );
        }

        $tags = array_values($tags);

        return StepResultFactory::switch(
            DeletePlanningTagsStep::STEP_KEY,
            [
                'tags' => $tags,
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
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $type = $data['type'];

        $headerKey = $type === 'task'
            ? 'tasking'
            : 'eventing';

        $text = $this->textResolver->get(
            'delete_tags',
            $messenger,
            $lang,
            [
                'tags' => TagFormatter::nums($tags),
            ]
        );

        return $context->messenger->format(
            $this->textResolver->header(
                $messenger,
                $lang,
                $headerKey,
                'task_step_tags'
            ),
            $text,
            $error
        );
    }
}
