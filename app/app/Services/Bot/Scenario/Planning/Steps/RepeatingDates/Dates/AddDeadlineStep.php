<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDatetimeFormatMessage;
use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Helpers\Dates\DatesParser;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddDeadlineStep implements StepInterface
{
    public const STEP_KEY = 'addDeadline';

    public function __construct(
        private readonly MessageIntentResolver      $intentResolver,
        private readonly MessengerTextResolver      $textResolver,
        private readonly DatesFormatter             $datesFormatter,
        private readonly WrongDatetimeFormatMessage $wrongDatetimeFormat,
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
            StepResultFactory::switch(
                SelectDateModeStep::STEP_KEY
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
        string     $message
    ): StepResultDTO
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $deadline = DatesParser::parseDateTimeString($message);

        if ($deadline === null) {
            return StepResultFactory::repeat(
                $this->wrongDatetimeFormat->get($context)
            );
        }

        $nextStep = ($data['repeating_type'] ?? 'no_repeat') === 'repeat'
            ? AddRepeatDaysIntervalStep::STEP_KEY
            : AddRemindersStep::STEP_KEY;

        return StepResultFactory::switch(
            $nextStep,
            [
                'deadline_date' => $deadline,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string    $error = null
    ): void
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $deadline = $data['deadline_date'] ?? null;

        $answer = $this->buildAnswer(
            $context,
            $messenger,
            $lang,
            $deadline,
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
        string     $messenger,
        string     $lang,
        ?array     $deadline,
        ?string    $error
    ): string
    {
        $headerKey = ScenarioHelper::headerKey($context);

        $textKey =
            $deadline
                ? 'enter_edit_deadline'
                : 'enter_deadline';

        $text = $this->textResolver->get(
            $textKey,
            $messenger,
            $lang,
            [
                'date' => $deadline
                    ? $this->datesFormatter->human(
                        $deadline,
                        $messenger,
                        $lang)
                    : null,
            ]
        );

        return $context->messenger->format(
            $this->textResolver->header(
                $messenger,
                $lang,
                $headerKey,
                'task_step_repeating'
            ),
            $text,
            $error
        );
    }
}
