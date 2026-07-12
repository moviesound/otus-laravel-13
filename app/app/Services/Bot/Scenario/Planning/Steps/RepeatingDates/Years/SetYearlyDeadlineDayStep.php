<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDayFormatMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SetYearlyDeadlineDayStep implements StepInterface
{
    public const STEP_KEY = 'setYearlyDeadlineDay';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDayFormatMessage $wrongDayFormatMessage,
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
                SelectYearlyDeadlineMonthStep::STEP_KEY
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
        if (!preg_match('/^\d{1,2}$/', $message)) {
            return StepResultFactory::repeat(
                $this->wrongDayFormatMessage->get($context)
            );
        }

        $day = (int) $message;

        if ($day < 1 || $day > 31) {
            return StepResultFactory::repeat(
                $this->wrongDayFormatMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            AddRemindersStep::STEP_KEY,
            [
                'day_in_year' => $day,
            ]
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $month = $data['month_in_year'] ?? null;

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'enter_day_in_year_month',
            $messenger,
            $lang,
            [
                'month' => $month,
            ]
        );

        $context->messenger?->sendMessage(
            $context->messenger->format($header, $body, $error),
            [
                MessageButtons::defaultActions(
                    textResolver: $this->textResolver,
                    messenger: $messenger,
                    lang: $lang,
                    backBtn: true,
                    cancelBtn: true,
                ),
            ]
        );
    }
}
