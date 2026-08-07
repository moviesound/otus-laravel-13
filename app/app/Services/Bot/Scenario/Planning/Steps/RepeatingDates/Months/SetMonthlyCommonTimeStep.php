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

final class SetMonthlyCommonTimeStep implements StepInterface
{
    public const STEP_KEY = 'setMonthlyCommonTime';

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
        $timeRange = TimeRangeParser::parse($message);

        if ($timeRange === false) {
            return StepResultFactory::repeat(
                $this->wrongTimeMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            AddRemindersStep::STEP_KEY,
            [
                'monthly_common_time' => $timeRange,
            ]
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

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
            $this->buildAnswer($context, $error),
            $buttons
        );
    }

    private function buildAnswer(
        BotContext $context,
        ?string $error
    ): string {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'add_time_or_period',
            $messenger,
            $lang
        );

        return $context->messenger->format($header, $body, $error);
    }
}
