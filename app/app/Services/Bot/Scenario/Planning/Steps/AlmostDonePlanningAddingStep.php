<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Helpers\Scenarios\Summeries\PlanningPreviewFormatter;
use App\Services\Bot\Scenario\Planning\Steps\Reminders\AddRemindersStep;
use App\Services\Bot\Scenario\StepResultFactory;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Messengers\MessengerTextResolver;

final class AlmostDonePlanningAddingStep implements StepInterface
{
    public const STEP_KEY = 'almostDoneAdding';

    public function __construct(
        private readonly PlanningPreviewFormatter $previewFormatter,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
        private readonly MessengerTextResolver $messengerTextResolver,
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

            MessageIntent::No =>
            StepResultFactory::switch(
                PlanningDoneStep::STEP_KEY
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
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        $message = $this->previewFormatter->format(
            $data,
            $messenger,
            $lang,
        );

        $buttons = $this->buildButtons($messenger, $lang);

        $context->messenger?->sendMessage(
            $context->messenger->format(
                $message,
                $error
            ),
            $buttons
        );
    }

    private function buildButtons(string $messenger, string $lang): array
    {
        return [
            [
                [
                    'text' => $this->text('confirm_creation', $messenger, $lang),
                    'callback_data' => 'no',
                ],
                [
                    'text' => $this->text('back', $messenger, $lang),
                    'callback_data' => 'back',
                ],
            ],
        ];
    }

    private function text(string $key, string $messenger, string $lang): string
    {
        return $this->messengerTextResolver->get($key, $messenger, $lang);
    }
}
