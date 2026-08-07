<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDayFormatMessage;
use App\Services\Bot\Errors\WrongQuarterPeriodMessage;
use App\Services\Bot\Helpers\Dates\QuarterPeriodValidator;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SetQuarterlyPeriodEndDayStep implements StepInterface
{
    public const STEP_KEY = 'setQuarterlyPeriodEndDay';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDayFormatMessage $wrongDayFormatMessage,
        private readonly WrongQuarterPeriodMessage $wrongQuarterPeriodMessage,
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
                SelectQuarterlyPeriodEndMonthStep::STEP_KEY
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
        if (!ctype_digit($message)) {
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

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        if (!QuarterPeriodValidator::isValid(
            (int)$data['start_month_in_quarter'],
            (int)$data['start_day_in_quarter'],
            (int)$data['end_month_in_quarter'],
            (int)$message,
        )) {
            return StepResultFactory::repeat(
                $this->wrongQuarterPeriodMessage->get(
                    $context,
                    $data['start_month_in_quarter'],
                    $data['end_month_in_quarter'],
                    $data['start_day_in_quarter'],
                    $message,
                )
            );
        }

        return StepResultFactory::switch(
            AddRemindersStep::STEP_KEY,
            [
                'end_day_in_quarter' => $day,
            ]
        );
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
                ? 'enter_day_in_end_quarter_month'
                : 'enter_day_in_end_quarter_month_event',
            $messenger,
            $lang
        );

        $buttons = [
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            ),
        ];

        $context->messenger?->sendMessage(
            $context->messenger->format($header, $body, $error),
            $buttons
        );
    }
}
