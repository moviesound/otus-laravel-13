<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Reminders;

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
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddRemindersStep implements StepInterface
{
    public const STEP_KEY = 'addReminders';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
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
                SelectRepeatingOrDateStep::STEP_KEY
            ),

            MessageIntent::Continue =>
            StepResultFactory::switch(
                AlmostDonePlanningAddingStep::STEP_KEY
            ),

            MessageIntent::Stop =>
            StepResultFactory::switch(
                PlanningDoneStep::STEP_KEY
            ),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(
        BotContext $context,
        string $message,
    ): StepResultDTO {

        return match ($message) {

            'reminder_hours' =>
            StepResultFactory::switch(
                SelectReminderValueStep::STEP_KEY,
                [],
                'hours'
            ),

            'reminder_days' =>
            StepResultFactory::switch(
                SelectReminderValueStep::STEP_KEY,
                [],
                'days'
            ),

            'reminder_weeks' =>
            StepResultFactory::switch(
                SelectReminderValueStep::STEP_KEY,
                [],
                'weeks'
            ),

            'reminder_months' =>
            StepResultFactory::switch(
                SelectReminderValueStep::STEP_KEY,
                [],
                'months'
            ),

            'delete_reminders' =>
            StepResultFactory::switch(
                DeleteReminderStep::STEP_KEY
            ),

            default =>
            StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            ),
        };
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_reminders'
        );

        $body = $this->buildAnswer($messenger, $lang, $data);

        $buttons = $this->buildButtons($data, $messenger, $lang);

        $context->messenger?->sendMessage(
            $context->messenger->format(
                $header,
                $body,
                $error
            ),
            $buttons
        );
    }

    private function buildAnswer(string $messenger, string $lang, array $data): string
    {
        $body = $this->textResolver->get(
            'ask_reminder_interval_' . $data['type'],
            $messenger,
            $lang,
        );

        if (!empty($data['reminders'])) {
            $body .= "\n\n";

            $body .= $this->textResolver->get(
                'list_reminders',
                $messenger,
                $lang,
                [
                    'reminders' => '', // сюда позже подставится formatter
                ]
            );
        }
        return $body;
    }

    private function buildButtons(array $data, string $messenger, string $lang): array
    {
        $buttons = [];

        if ($this->hasTime($data)) {
            $buttons[] = [[
                'text' => $this->textResolver->get('enter_hours', $messenger, $lang),
                'callback_data' => 'reminder_hours',
            ]];
        }

        $buttons[] = [[
            'text' => $this->textResolver->get('enter_days', $messenger, $lang),
            'callback_data' => 'reminder_days',
        ]];

        $buttons[] = [[
            'text' => $this->textResolver->get('enter_weeks', $messenger, $lang),
            'callback_data' => 'reminder_weeks',
        ]];

        $buttons[] = [[
            'text' => $this->textResolver->get('enter_months', $messenger, $lang),
            'callback_data' => 'reminder_months',
        ]];

        if (!empty($data['reminders'])) {
            $buttons[] = [[
                'text' => $this->textResolver->get('delete_reminders', $messenger, $lang),
                'callback_data' => 'delete_reminders',
            ]];
        }

        $buttons[] =  MessageButtons::defaultActions(
            textResolver: $this->textResolver,
            messenger: $messenger,
            lang: $lang,
            backBtn: true,
            skipBtn: true,
            cancelBtn: true,
        );

        return $buttons;
    }

    private function hasTime(array $data): bool
    {
        return
            !empty($data['time']) ||
            !empty($data['common_time']) ||
            !empty($data['weekly_common_time']) ||
            !empty($data['monthly_common_time']) ||
            !empty($data['quarterly_common_time']) ||
            !empty($data['yearly_common_time']);
    }
}
