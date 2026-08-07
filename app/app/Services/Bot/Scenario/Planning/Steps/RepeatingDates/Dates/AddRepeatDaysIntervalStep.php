<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongNumberMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\Repeating\RepeatDateResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatTypeStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddRepeatDaysIntervalStep implements StepInterface
{
    public const STEP_KEY = 'addRepeatDaysInterval';

    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly RepeatDateResolver    $repeatDateResolver,
        private readonly WrongNumberMessage    $wrongNumber,
    ) {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(
        BotContext $context
    ): StepResultDTO {
        $message = trim(
            $context->messageDTO->value()
        );

        $intent = ($this->intentResolver)(
            $message
        );

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
            $this->handleMessage(
                $context,
                $message
            ),
        };
    }

    private function handleMessage(
        BotContext $context,
        string $message
    ): StepResultDTO {
        if (!ctype_digit($message)) {
            return StepResultFactory::repeat($this->wrongNumber->get($context));
        }

        $days = (int)$message;

        if ($days < 0) {
            return StepResultFactory::repeat($this->wrongNumber->get($context));
        }

        return StepResultFactory::switch(
            AddRemindersStep::STEP_KEY,
            [
                'repeat_interval' => $days + 1,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {

        [$messenger, $lang] =
            MessageContext::getMessengerAndLang(
                $context
            );

        $answer = $this->buildAnswer(
            $context,
            $messenger,
            $lang,
            $error
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
            $answer,
            $buttons
        );
    }

    private function buildAnswer(
        BotContext $context,
        string $messenger,
        string $lang,
        ?string $error
    ): string {

        $text = $this->textResolver->get(
            'enter_repeat_every_n_days',
            $messenger,
            $lang
        );

        $preview = $this->buildPreview(
            $context,
            $messenger,
            $lang
        );

        if ($preview) {
            $text .= "\n\n" . $preview;
        }

        return $context->messenger->format(
            $this->textResolver->header(
                $messenger,
                $lang,
                ScenarioHelper::headerKey(
                    $context
                ),
                'task_step_repeating'
            ),
            $text,
            $error
        );
    }

    private function buildPreview(
        BotContext $context,
        string $messenger,
        string $lang
    ): ?string {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        if (
            !isset($data['repeat_interval'])
        ) {
            return null;
        }

        $date =
            ($data['date_mode'] ?? null) === 'period'
                ? ($data['period_end']
                ?? $data['period_start']
                ?? null)
                : ($data['deadline_date']
                ?? null);

        if (!$date) {
            return null;
        }

        $nextDate =
            $this->repeatDateResolver
                ->resolveNextDate(
                    $date,
                    $context->userDTO,
                    (int)$data['repeat_interval']
                );

        if (!$nextDate) {
            return null;
        }

        return $this->textResolver->get(
            'repeat_days_start',
            $messenger,
            $lang,
            [
                'date' => $nextDate->format(
                    'd.m.Y H:i'
                ),
            ]
        );
    }
}
