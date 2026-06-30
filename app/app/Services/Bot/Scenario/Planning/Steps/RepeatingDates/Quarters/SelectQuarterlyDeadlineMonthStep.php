<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Quarters;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectQuarterlyDeadlineMonthStep implements StepInterface
{
    public const STEP_KEY = 'selectQuarterlyDeadlineMonth';

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
                SelectQuarterlyTypeStep::STEP_KEY
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
        if (str_starts_with($message, 'miq_')) {
            $month = (int) substr($message, 4);

            if (!in_array($month, [1, 2, 3], true)) {
                return StepResultFactory::repeat(
                    $this->wrongDataUseButtonsMessage->get($context)
                );
            }

            return StepResultFactory::switch(
                SetQuarterlyDeadlineDayStep::STEP_KEY,
                [
                    'month_in_quarter' => $month,
                    'repeat_type' => 'quarterly',
                    'year_type' => 'deadline',
                ]
            );
        } else {
            if (!in_array($message, [1, 2, 3, '1', '2', '3'], true)) {
                return StepResultFactory::repeat(
                    $this->wrongDataUseButtonsMessage->get($context)
                );
            } else {
                return StepResultFactory::switch(
                    SetQuarterlyDeadlineDayStep::STEP_KEY,
                    [
                        'month_in_quarter' => $message,
                        'repeat_type' => 'quarterly',
                        'year_type' => 'deadline',
                    ]
                );
            }
        }
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
                ? 'choose_month_in_quarter'
                : 'choose_month_in_quarter_event',
            $messenger,
            $lang
        );

        $buttons = $this->buildButtons($messenger, $lang);

        $context->messenger?->sendMessage(
            $context->messenger->format($header, $body, $error),
            $buttons
        );
    }

    private function buildButtons(string $messenger, string $lang): array
    {
        return [
            [
                [
                    'text' => '1',
                    'callback_data' => 'miq_1',
                ],
                [
                    'text' => '2',
                    'callback_data' => 'miq_2',
                ],
                [
                    'text' => '3',
                    'callback_data' => 'miq_3',
                ],
            ],
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            ),
        ];
    }

    private function buildAnswer(BotContext $context, ?string $error): string
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'choose_month_in_quarter',
            $messenger,
            $lang
        );

        return $context->messenger->format($header, $body, $error);
    }
}
