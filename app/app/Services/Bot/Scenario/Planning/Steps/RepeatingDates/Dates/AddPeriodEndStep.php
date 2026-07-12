<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDatetimeFormatMessage;
use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Helpers\Dates\DatesParser;
use App\Services\Bot\Helpers\Dates\TimeResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddPeriodEndStep implements StepInterface
{
    public const STEP_KEY = 'addPeriodEnd';

    public function __construct(
        private readonly MessageIntentResolver      $intentResolver,
        private readonly MessengerTextResolver      $textResolver,
        private readonly DatesFormatter             $datesFormatter,
        private readonly WrongDatetimeFormatMessage $wrongDatetimeFormat,
        private readonly TimeResolver               $timeResolver,
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
                AddPeriodStartStep::STEP_KEY
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
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        if ($message === 'skip') {
            $periodStart = $data['period_start'];

            $time = $this->timeResolver->resolve(
                $context->userDTO,
                $periodStart
            );

            $periodEnd = array_merge($periodStart, $time);

            $nextStep = $this->nextStep($data);

            return StepResultFactory::switch(
                $nextStep,
                [
                    'period_end' => $periodEnd,
                    'time_set_by_user' => 0,
                ]
            );
        }

        /**
         * PARSE USER INPUT
         */
        $parsed = DatesParser::parseDateTimeString($message);

        if (!$parsed) {
            return StepResultFactory::repeat(
                $this->wrongDatetimeFormat->get($context)
            );
        }

        return StepResultFactory::switch(
            $this->nextStep($data),
            [
                'period_end' => $parsed,
                'time_set_by_user' => 1,
            ]
        );
    }

    private function nextStep(array $data): string
    {
        return ($data['repeating_type'] ?? 'no_repeat') === 'repeat'
            ? AddRepeatDaysIntervalStep::STEP_KEY
            : AddRemindersStep::STEP_KEY;
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $periodEnd = $data['period_end'] ?? null;

        $type = $data['type'];

        $headerKey = $type === 'task'
            ? 'tasking'
            : 'eventing';

        $textKey = $periodEnd
            ? 'enter_edit_period_end'
            : 'enter_period_end_or_skip';

        $params = [];

        if ($periodEnd) {
            $params['date'] = $this->datesFormatter->human(
                $periodEnd,
                $messenger,
                $lang
            );
        }

        $text = $this->textResolver->get(
            $textKey,
            $messenger,
            $lang,
            $params
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
                $this->textResolver->header(
                    $messenger,
                    $lang,
                    $headerKey,
                    'task_step_repeating'
                ),
                $text,
                $error
            ),
            $buttons
        );
    }
}
