<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\Reminders\ReminderFormatter;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\AlmostDonePlanningAddingStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\SelectRepeatingOrDateStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddRemindersStep implements StepInterface
{
    public const STEP_KEY = 'addReminders';

    public function __construct(
        private readonly MessengerTextResolver      $textResolver,
        private readonly MessageIntentResolver      $intentResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
        private readonly ReminderFormatter          $reminderFormatter,
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
        string     $message,
    ): StepResultDTO
    {

        $reminderTypes = [
            'reminder_hours' => 'hours',
            'reminder_days' => 'days',
            'reminder_weeks' => 'weeks',
            'reminder_months' => 'months',
        ];

        if (isset($reminderTypes[$message])) {
            return StepResultFactory::switch(
                SelectReminderValueStep::STEP_KEY,
                [],
                $reminderTypes[$message]
            );
        }

        if ($message === 'delete_reminders') {
            return StepResultFactory::switch(
                DeleteReminderStep::STEP_KEY
            );
        }

        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context)
        );
    }

    public function show(
        BotContext $context,
        ?string    $error = null
    ): void
    {
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
                    'reminders' => $this->reminderFormatter->list(
                        $data['reminders'] ?? [],
                        $messenger,
                        $lang,
                    ),
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

        $buttons[] = MessageButtons::defaultActions(
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
            !empty($data['yearly_common_time']) ||
            isset($data['date_mode']) && $data['date_mode'] === 'deadline' &&
                !empty($data['deadline_date']) &&
                isset($data['deadline_date']['hour'], $data['deadline_date']['minute']) ||
            $data['type'] === 'event' &&
                isset($data['date_mode']) && $data['date_mode'] === 'period' &&
                !empty($data['period_start']) &&
                isset($data['period_start']['hour'], $data['period_start']['minute']) ||
            $data['type'] === 'task' &&
                isset($data['date_mode']) && $data['date_mode'] === 'period' &&
                !empty($data['period_end']) &&
                isset($data['period_end']['hour'], $data['period_end']['minute']);
    }
}
