<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongReminderNumberMessage;
use App\Services\Bot\Errors\WrongTagNumberMessage;
use App\Services\Bot\Helpers\ReminderFormatter;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class DeleteReminderStep implements StepInterface
{
    public const STEP_KEY = 'deleteReminder';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly ReminderFormatter $reminderFormatter,
        private readonly WrongTagNumberMessage $wrongReminderNumberMessage,
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
                AddRemindersStep::STEP_KEY
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
        if (!ctype_digit($message)) {
            return StepResultFactory::repeat(
                $this->wrongReminderNumberMessage->get($context)
            );
        }

        $index = (int)$message - 1;

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        $reminders = $data['reminders'] ?? [];

        if (!isset($reminders[$index])) {
            return StepResultFactory::repeat(
                $this->wrongReminderNumberMessage->get($context)
            );
        }

        unset($reminders[$index]);

        return StepResultFactory::switch(
            self::STEP_KEY,
            [
                'reminders' => array_values($reminders),
            ]
        );
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

        $body = $this->textResolver->get(
            'delete_reminders_step',
            $messenger,
            $lang,
            [
                'reminders' => $this->reminderFormatter->list(
                    $data['reminders'] ?? [],
                ),
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
            $context->messenger->format(
                $header,
                $body,
                $error
            ),
            $buttons
        );
    }
}
