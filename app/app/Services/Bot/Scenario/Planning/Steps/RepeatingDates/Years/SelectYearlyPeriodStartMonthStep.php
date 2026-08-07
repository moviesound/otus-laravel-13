<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Years;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectYearlyPeriodStartMonthStep implements StepInterface
{
    public const STEP_KEY = 'selectYearlyPeriodStartMonth';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
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
                SelectYearlyTypeStep::STEP_KEY
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
        if (!str_starts_with($message, 'miq_') && !in_array($message, range(1, 12), true)) {
            return StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            );
        }

        $month = str_starts_with($message, 'miq_')
            ? (int) substr($message, 4)
            : (int) $message;

        if (!in_array($month, range(1, 12))) {
            return StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            SetYearlyPeriodStartDayStep::STEP_KEY,
            [
                'start_month_in_year' => $month,
                'repeat_type' => 'yearly',
                'year_type' => 'period',
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
                ? 'choose_start_month_in_year'
                : 'choose_start_month_in_year_event',
            $messenger,
            $lang
        );

        $context->messenger?->sendMessage(
            $context->messenger->format($header, $body, $error),
            [
                MessageButtons::defaultActions(
                    textResolver: $this->textResolver,
                    messenger: $messenger,
                    lang: $lang,
                    backBtn: true,
                    cancelBtn: true,
                ),
            ]
        );
    }
}
