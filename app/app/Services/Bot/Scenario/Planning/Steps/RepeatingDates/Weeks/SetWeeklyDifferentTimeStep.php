<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Weeks;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongTimeMessage;
use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Helpers\Dates\TimeRangeParser;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SetWeeklyDifferentTimeStep implements StepInterface
{
    public const STEP_KEY = 'setWeeklyDifferentTime';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongTimeMessage      $wrongTimeMessage,
        private readonly DatesFormatter        $datesFormatter,
    )
    {
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
            StepResultFactory::switch(SelectWeekDaysStep::STEP_KEY),

            MessageIntent::Stop =>
            StepResultFactory::switch(AddRemindersStep::STEP_KEY),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        if (!isset($data['week_days']) || empty($data['week_days'])) {
            return StepResultFactory::switch(
                SelectWeekDaysStep::STEP_KEY,
                [],
                null
            );
        }

        $weekDays = $this->weekDaysToArray($data['week_days']);
        $additionalInfo = $context->scenarioDTO->additionalInfo;

        $timeRange = TimeRangeParser::parse($message);

        if ($timeRange === false) {
            return StepResultFactory::repeat(
                $this->wrongTimeMessage->get($context)
            );
        }


        $existing = $data['weekly_different_time'] ?? [];
        $existing[$additionalInfo] = $timeRange;

        $nextWeekDay = $this->resolveWeekDay($weekDays, $additionalInfo);
        if ($nextWeekDay === null) {
            return StepResultFactory::switch(
                AddRemindersStep::STEP_KEY,
                [
                    'weekly_different_time' => $existing,
                ],
                null
            );
        }

        return StepResultFactory::switch(
            self::STEP_KEY,
            [
                'weekly_different_time' => $existing,
            ],
            (string)$nextWeekDay
        );
    }

    private function resolveWeekDay(array $weekDays, mixed $current): ?int
    {
        if ($current === null) {
            return (int)$weekDays[0];
        }

        $current = (int)$current;

        foreach ($weekDays as $index => $day) {
            if ((int)$day === $current) {
                return $weekDays[$index + 1] ?? null;
            }
        }

        return null;
    }

    private function weekDaysToArray(string $weekDays): array
    {
        $arr = explode(',', $weekDays);

        $arr = array_filter($arr, fn($v) => $v !== '');

        $arr = array_map('trim', $arr);
        $arr = array_map('intval', $arr);

        sort($arr, SORT_NUMERIC);

        return $arr;
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $weekDays = $this->weekDaysToArray($data['week_days'] ?? '');
        $weekDay = $context->scenarioDTO->additionalInfo !== null
            ? (int)$context->scenarioDTO->additionalInfo
            : ($weekDays[0] ?? null);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $answer = $this->textResolver->get('weekly_different_time',
            $messenger,
            $lang,
            [
                'day' => $weekDay
                    ? $this->datesFormatter->weekdayName(
                        $weekDay,
                        $messenger,
                        $lang,
                        false,
                        false,
                        true
                    )
                    : '',
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
            $context->messenger->format($header, $answer, $error),
            $buttons
        );
    }
}
