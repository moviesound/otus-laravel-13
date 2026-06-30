<?php

namespace App\Services\Bot\Scenario\Planning\Steps\RepeatingDates;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Enums\Bot\RepeatType;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\Repeating\RepeatingInfoText;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\PlanningDoneStep;
use App\Services\Bot\Scenario\Planning\Steps\RepeatingDates\Dates\SelectDateModeStep;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectRepeatTypeStep implements StepInterface
{
    public const STEP_KEY = 'selectRepeatType';

    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
        private readonly RepeatingInfoText $repeatingInfoText,
    ) {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(
        BotContext $context
    ): StepResultDTO {

        $message = trim(
            $context->messageDTO->value()
        );

        $intent = ($this->intentResolver)(
            $message
        );

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

        $repeatType = ScenarioHelper::repeatType(
            $message
        );

        if ($repeatType === null) {
            return StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            ScenarioHelper::repeatTypeCallback(
                $repeatType
            ),
            [
                'repeat_type' => $repeatType->value,
            ]
        );
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        [$messenger, $lang] =
            MessageContext::getMessengerAndLang(
                $context
            );

        $buttons = [
            ...$this->repeatTypeButtons(
                $messenger,
                $lang
            ),
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            ),
        ];

        $context->messenger?->sendMessage(
            $this->buildAnswer(
                $context,
                $messenger,
                $lang,
                $data,
                $error
            ),
            $buttons
        );
    }

    private function repeatTypeButtons(
        string $messenger,
        string $lang
    ): array {

        return [
            [
                $this->repeatTypeButton(
                    RepeatType::Daily,
                    $messenger,
                    $lang
                ),
            ],
            [
                $this->repeatTypeButton(
                    RepeatType::Weekly,
                    $messenger,
                    $lang
                ),
                $this->repeatTypeButton(
                    RepeatType::Monthly,
                    $messenger,
                    $lang
                ),
            ],
            [
                $this->repeatTypeButton(
                    RepeatType::Quarterly,
                    $messenger,
                    $lang
                ),
                $this->repeatTypeButton(
                    RepeatType::Yearly,
                    $messenger,
                    $lang
                ),
            ],
        ];
    }

    private function repeatTypeButton(
        RepeatType $type,
        string $messenger,
        string $lang
    ): array {

        return [
            'text' => $this->textResolver->get(
                $type->value,
                $messenger,
                $lang
            ),
            'callback_data' => $type->value,
        ];
    }

    private function buildAnswer(
        BotContext $context,
        string $messenger,
        string $lang,
        array $data,
        ?string $error
    ): string {
        $header = $this->textResolver->header(
            $messenger,
            $lang,
            ScenarioHelper::headerKey(
                $context
            ),
            'task_step_repeating'
        );

        $body = $this->textResolver->get(
            'set_repeating_type',
            $messenger,
            $lang
        );

        $info = isset($data['repeat_type']) && !empty($data['repeat_type'])
            ? "\n\n" . $this->repeatingInfoText->build($data, $messenger, $lang)
            : '';

        return $context->messenger->format(
            $header,
            $body . $info,
            $error
        );
    }
}
