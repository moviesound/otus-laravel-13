<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Months;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongTimeMessage;
use App\Services\Bot\Helpers\Dates\TimeRangeParser;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SetMonthlyDifferentTimeStep implements StepInterface
{
    public const STEP_KEY = 'setMonthlyDifferentTime';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongTimeMessage $wrongTimeMessage,
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
                AskMonthlyTimeAddStep::STEP_KEY
            ),

            MessageIntent::Stop =>
            StepResultFactory::switch(
                AddRemindersStep::STEP_KEY
            ),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        if (empty($data['month_days'])) {
            return StepResultFactory::switch(
                AskMonthlyTimeAddStep::STEP_KEY
            );
        }

        $days = $this->monthDaysToArray($data['month_days']);
        $additionalInfo = $context->scenarioDTO->additionalInfo;

        $timeRange = TimeRangeParser::parse($message);

        if ($timeRange === false) {
            return StepResultFactory::repeat(
                $this->wrongTimeMessage->get($context)
            );
        }

        $existing = $data['monthly_different_time'] ?? [];
        $existing[$additionalInfo] = $timeRange;

        $nextDay = $this->resolveNextDay($days, $additionalInfo);

        if ($nextDay === null) {
            return StepResultFactory::switch(
                AddRemindersStep::STEP_KEY,
                [
                    'monthly_different_time' => $existing,
                ],
                null
            );
        }

        return StepResultFactory::switch(
            self::STEP_KEY,
            [
                'monthly_different_time' => $existing,
            ],
            (string)$nextDay
        );
    }

    private function resolveNextDay(array $days, mixed $current): ?int
    {
        if ($current === null) {
            return (int)$days[0];
        }

        $current = (int)$current;

        foreach ($days as $i => $day) {
            if ((int)$day === $current) {
                return $days[$i + 1] ?? null;
            }
        }

        return null;
    }

    private function monthDaysToArray(string $monthDays): array
    {
        $arr = explode(',', $monthDays);

        $arr = array_map('trim', $arr);

        $arr = array_filter($arr, fn ($v) => is_numeric($v));

        $arr = array_map('intval', $arr);

        sort($arr, SORT_NUMERIC);

        return $arr;
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $day = $context->scenarioDTO->additionalInfo;

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'monthly_different_time',
            $messenger,
            $lang,
            [
                'day' => $day ?? '',
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
