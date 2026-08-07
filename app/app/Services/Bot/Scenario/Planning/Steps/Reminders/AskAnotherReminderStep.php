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
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\Planning\Steps\AlmostDonePlanningAddingStep;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class AskAnotherReminderStep implements StepInterface
{
    public const STEP_KEY = 'askAnotherReminder';

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
            'reminder_yes' =>
            StepResultFactory::switch(
                AddRemindersStep::STEP_KEY
            ),

            'reminder_no' =>
            StepResultFactory::switch(
                AlmostDonePlanningAddingStep::STEP_KEY
            ),

            default =>
            StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            ),
        };
    }

    public function show(
        BotContext $context,
        ?string $error = null,
    ): void {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_reminders'
        );

        $body = $this->textResolver->get(
            'ask_another_reminder',
            $messenger,
            $lang,
        );

        $buttons = [
            [[
                'text' => $this->textResolver->get('yes', $messenger, $lang),
                'callback_data' => 'reminder_yes',
            ]],
            [[
                'text' => $this->textResolver->get('no', $messenger, $lang),
                'callback_data' => 'reminder_no',
            ]],
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                cancelBtn: true,
            )
        ];

        $context->messenger?->sendMessage(
            $context->messenger->format(
                $header,
                $body,
                $error,
            ),
            $buttons
        );
    }
}
