<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDayNumberMessage;
use App\Services\Bot\Errors\WrongMonthDaysFormatMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatTypeStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectMonthDaysStep implements StepInterface
{
    public const STEP_KEY = 'selectMonthDays';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDayNumberMessage $wrongDayNumberMessage,
        private readonly WrongMonthDaysFormatMessage $wrongMonthDaysFormatMessage,
    ) {
    }

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
                SelectRepeatTypeStep::STEP_KEY,
            ),

            MessageIntent::Stop =>
            StepResultFactory::switch(
                PlanningDoneStep::STEP_KEY
            ),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(
        BotContext $context,
        string $message
    ): StepResultDTO {
        $clean = trim($message);
        if (!preg_match('/^[0-9,\-\s]+$/u', $clean)) {
            return StepResultFactory::repeat(
                $this->wrongMonthDaysFormatMessage->get($context)
            );
        }

        $normalized = preg_replace('/\s*-\s*/', '-', $clean);

        $parts = preg_split('/\s*,\s*/', $normalized);

        foreach ($parts as $part) {

            if (str_contains($part, '-')) {

                [$start, $end] = array_map(
                    'intval',
                    explode('-', $part, 2)
                );

                if (
                    $start < 1 ||
                    $end > 31 ||
                    $start > $end
                ) {
                    return StepResultFactory::repeat(
                        $this->wrongDayNumberMessage->get($context)
                    );
                }

                continue;
            }

            $day = (int)$part;

            if ($day < 1 || $day > 31) {
                return StepResultFactory::repeat(
                    $this->wrongDayNumberMessage->get($context)
                );
            }
        }

        $onlyRanges = preg_match(
            '/^\s*\d+-\d+(?:\s*,\s*\d+-\d+)*\s*$/',
            $normalized
        );

        return StepResultFactory::switch(
            $onlyRanges
                ? AddRemindersStep::STEP_KEY
                : AskMonthlyTimeAddStep::STEP_KEY,
            [
                'month_days' => $normalized,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'enter_month_days',
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
            $context->messenger->format(
                $header,
                $body,
                $error
            ),
            $buttons
        );
    }
}
