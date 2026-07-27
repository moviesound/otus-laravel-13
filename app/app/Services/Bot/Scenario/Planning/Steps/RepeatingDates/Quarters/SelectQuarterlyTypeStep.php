<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters;

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
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectQuarterlyTypeStep implements StepInterface
{
    public const STEP_KEY = 'selectQuarterlyType';

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
                SelectRepeatTypeStep::STEP_KEY
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
            'deadline' =>
            StepResultFactory::switch(
                SelectQuarterlyDeadlineMonthStep::STEP_KEY
            ),

            'period' =>
            StepResultFactory::switch(
                SelectQuarterlyPeriodStartMonthStep::STEP_KEY
            ),

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
        $type = $data['type'] ?? null;

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            $type === 'task'
                ? 'task_enter_date_or_period'
                : 'event_enter_date_or_period',
            $messenger,
            $lang
        );

        $buttons = $this->buildButtons($messenger, $lang);

        $context->messenger?->sendMessage(
            $context->messenger->format($header, $body, $error),
            $buttons
        );
    }

    private function buildButtons(string $messenger, string $lang): array
    {
        return [
            [
                [
                    'text' => $this->textResolver->get('deadline_emo', $messenger, $lang),
                    'callback_data' => 'deadline',
                ],
                [
                    'text' => $this->textResolver->get('period_emo', $messenger, $lang),
                    'callback_data' => 'period',
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
