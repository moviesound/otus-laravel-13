<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDatetimeFormatMessage;
use App\Services\Bot\Helpers\Dates\DatesFormatter;
use App\Services\Bot\Helpers\Dates\DatesParser;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddPeriodStartStep implements StepInterface
{
    public const STEP_KEY = 'addPeriodStart';

    public function __construct(
        private readonly MessageIntentResolver      $intentResolver,
        private readonly MessengerTextResolver      $textResolver,
        private readonly DatesFormatter             $datesFormatter,
        private readonly WrongDatetimeFormatMessage $wrongDatetimeFormat,
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
        string $message
    ): StepResultDTO {

        $parsed = DatesParser::parseDateTimeString($message);

        if ($parsed === null) {
            return StepResultFactory::repeat(
                $this->wrongDatetimeFormat->get($context)
            );
        }

        return StepResultFactory::switch(
            AddPeriodEndStep::STEP_KEY,
            [
                'period_start' => $parsed,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {

        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

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
            )
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
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $headerKey = ScenarioHelper::headerKey($context);

        $periodStart = $data['period_start'] ?? null;

        $textKey = $periodStart
            ? 'enter_edit_period_start'
            : 'enter_period_start';

        $params = [];

        if ($periodStart) {
            $params['date'] =
                $this->datesFormatter->human(
                    $periodStart,
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
