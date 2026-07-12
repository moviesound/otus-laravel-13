<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Reminders;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongNumberMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectReminderValueStep implements StepInterface
{
    public const STEP_KEY = 'selectReminderValue';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongNumberMessage $wrongNumberMessage,
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

        if (!is_numeric($message)) {
            return StepResultFactory::repeat(
                $this->wrongNumberMessage->get($context)
            );
        }

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        $reminders = $data['reminders'] ?? [];

        $reminders[] = [
            'type' => $context->scenarioDTO->additionalInfo,
            'value' => (int)$message,
        ];

        return StepResultFactory::switch(
            AddReminderTextStep::STEP_KEY,
            [
                'reminders' => $reminders,
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
            'enter_number_for_' . $context->scenarioDTO->additionalInfo,
            $messenger,
            $lang,
        );

        $buttons = [[
            [
                'text' => $this->textResolver->get('back', $messenger, $lang),
                'callback_data' => 'back',
            ],
            [
                'text' => $this->textResolver->get('cancel', $messenger, $lang),
                'callback_data' => 'cancel',
            ],
        ]];

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
