<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AskMonthlyTimeAddStep implements StepInterface
{
    public const STEP_KEY = 'askMonthlyTimeAdd';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
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
            StepResultFactory::switch(
                SelectMonthDaysStep::STEP_KEY
            ),

            MessageIntent::Stop =>
            StepResultFactory::switch(
                PlanningDoneStep::STEP_KEY
            ),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        return match ($message) {

            'yes_one' =>
            StepResultFactory::switch(SetMonthlyCommonTimeStep::STEP_KEY),

            'yes_many' =>
            StepResultFactory::switch(SetMonthlyDifferentTimeStep::STEP_KEY),

            'no' =>
            StepResultFactory::switch(AddRemindersStep::STEP_KEY),

            default =>
            StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            ),
        };
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $type = $data['type'] ?? 'task';

        $buttons = $this->buildButtons($messenger, $lang);

        $context->messenger?->sendMessage(
            $this->buildAnswer($context, $messenger, $lang, $type, $error),
            $buttons
        );
    }

    private function buildButtons(string $messenger, string $lang): array
    {
        return [
            [
                [
                    'text' => $this->textResolver->get('yes_one', $messenger, $lang),
                    'callback_data' => 'yes_one',
                ],
            ],
            [
                [
                    'text' => $this->textResolver->get('yes_many', $messenger, $lang),
                    'callback_data' => 'yes_many',
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

    private function buildAnswer(
        BotContext $context,
        string $messenger,
        string $lang,
        string $type,
        ?string $error,
    ): string {
        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'ask_monthly_repeats_need_time',
            $messenger,
            $lang,
        );

        return $context->messenger->format($header, $body, $error);
    }
}
