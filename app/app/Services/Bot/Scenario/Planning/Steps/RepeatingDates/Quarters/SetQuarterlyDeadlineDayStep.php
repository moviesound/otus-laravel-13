<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDayFormatMessage;
use App\Services\Bot\Errors\WrongDayNumberMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SetQuarterlyDeadlineDayStep implements StepInterface
{
    public const STEP_KEY = 'setQuarterlyDeadlineDay';

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
                SelectQuarterlyDeadlineMonthStep::STEP_KEY
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
        $clean = trim($message);

        // ожидаем: 1-31 или диапазон 5-10, 1,5,10-12
        if (!preg_match('/^[0-9,\-\s]+$/u', $clean)) {
            return StepResultFactory::repeat(
                $this->wrongDayFormatMessage->get($context)
            );
        }

        $normalized = preg_replace('/\s*-\s*/', '-', $clean);
        $parts = preg_split('/\s*,\s*/', $normalized);

        foreach ($parts as $part) {

            if (str_contains($part, '-')) {
                [$start, $end] = array_map('intval', explode('-', $part, 2));

                if ($start < 1 || $end > 31 || $start > $end) {
                    return StepResultFactory::repeat(
                        $this->wrongDayFormatMessage->get($context)
                    );
                }

                continue;
            }

            $day = (int) $part;

            if ($day < 1 || $day > 31) {
                return StepResultFactory::repeat(
                    $this->wrongDayFormatMessage->get($context)
                );
            }
        }

        return StepResultFactory::switch(
            AddRemindersStep::STEP_KEY,
            [
                'day_in_quarter' => $normalized,
            ]
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $month = $data['month_in_quarter'] ?? null;

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'enter_day_in_quarter_month',
            $messenger,
            $lang,
            [
                'month' => $month,
            ]
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
