<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\SelectDateModeStep;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\StepResultFactory;


final class SelectRepeatingOrDateStep implements StepInterface
{
    public const STEP_KEY = 'selectRepeatingOrDate';

    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
    ) {}

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = trim($context->messageDTO->value());
        $intent = ($this->intentResolver)($message);

        return match ($intent) {

            MessageIntent::Back =>
            StepResultFactory::switch(AddPlanningTagsStep::STEP_KEY),

            MessageIntent::Stop =>
            StepResultFactory::switch(PlanningDoneStep::STEP_KEY),

            default => $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        if ($message === 'no_repeat') {
            return StepResultFactory::switch(
                SelectDateModeStep::STEP_KEY,
                [
                    'repeating_type' => 'no_repeat',
                    'repeat_type' => 'none',
                ]
            );
        }

        if ($message === 'repeat') {
            return StepResultFactory::switch(
                SelectDateModeStep::STEP_KEY,
                [
                    'repeating_type' => 'repeat',
                ]
            );
        }

        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context),
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $answer = $this->buildAnswer(
            $context,
            $messenger,
            $lang,
            $error
        );

        $buttons = $this->buildButtons(
            $messenger,
            $lang
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
    ): string
    {
        $headerKey = ScenarioHelper::headerKey($context);

        $text = $this->textResolver->get(
            'select_repeat_or_date',
            $messenger,
            $lang
        );

        return $context->messenger->format(
            $this->textResolver->header($messenger, $lang, $headerKey, 'task_step_repeating'),
            $text,
            $error
        );
    }

    private function buildButtons(
        string $messenger,
        string $lang
    ): array
    {
        return [
            [
                [
                    'text' => $this->textResolver->get('no_repeat_task', $messenger, $lang),
                    'callback_data' => 'no_repeat',
                ],
            ],
            [
                [
                    'text' => $this->textResolver->get('repeat_task', $messenger, $lang),
                    'callback_data' => 'repeat',
                ],
            ],
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            ),
        ];
    }
}
