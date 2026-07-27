<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\StepResultFactory;

final class AddReminderTextStep implements StepInterface
{
    public const STEP_KEY = 'addReminderText';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
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

            MessageIntent::Continue =>
            StepResultFactory::switch(
                AskAnotherReminderStep::STEP_KEY
            ),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(
        BotContext $context,
        string $message,
    ): StepResultDTO {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        $reminders = $data['reminders'] ?? [];

        if (empty($reminders)) {
            return StepResultFactory::switch(
                AddRemindersStep::STEP_KEY
            );
        }

        $lastIndex = array_key_last($reminders);

        $reminders[$lastIndex]['text'] = $message;

        return StepResultFactory::switch(
            AskAnotherReminderStep::STEP_KEY,
            [
                'reminders' => $reminders,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {

        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey($context),
            'task_step_reminders'
        );

        $body = $this->textResolver->get(
            'enter_optional_reminder_text',
            $messenger,
            $lang,
        );

        $buttons = [
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                skipBtn: true,
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
