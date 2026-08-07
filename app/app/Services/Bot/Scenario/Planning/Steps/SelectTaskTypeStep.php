<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

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
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectTaskTypeStep implements StepInterface
{
    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
    ) {}

    public const STEP_KEY = 'selectTaskType';

    public static function stepKey(): string
    {
        return static::STEP_KEY
            ?? throw new \LogicException('Step KEY not defined');
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = $context->messageDTO->value();
        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Stop =>
            StepResultFactory::switch(PlanningDoneStep::STEP_KEY),

            MessageIntent::Back =>
            StepResultFactory::switch(SelectPlanTypeStep::STEP_KEY),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(
        BotContext $context,
        string $message
    ): StepResultDTO {
        if (!in_array($message, config('steps.tasks'), true)) {
            return StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            AddPlanningTitleStep::STEP_KEY,
            [
                'subType' => $message,
            ]
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);
        $isEdit = $this->isEditMode($context);

        $answer = $this->getAnswer($messenger, $lang, $error, $isEdit, $context);
        $buttons = $this->getButtons($messenger, $lang, $isEdit);

        $context->messenger?->sendMessage($answer, $buttons);
    }

    private function getAnswer(string $messenger, string $lang, ?string $error, bool $isEdit, BotContext $context): string
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);

        $textKey = $isEdit
            ? 'task_change_task_cath'
            : 'task_choose_task_cath';

        $replace = [];

        if ($isEdit) {
            $replace = [
                'type' => $this->textResolver->get(
                    $data['subType'],
                    $messenger,
                    $lang
                ),
            ];
        }

        $answer = $context->messenger->format(
            $this->textResolver->header($messenger, $lang, 'tasking', 'task_step_type'),
            $this->textResolver->get($textKey, $messenger, $lang, $replace),
            $error
        );

        return $answer;
    }

    private function getButtons(string $messenger, string $lang, bool $isEdit = false): array
    {
        $buttons = MessageButtons::make(
            items: config('steps.tasks'),
            textResolver: $this->textResolver,
            messenger: $messenger,
            lang: $lang,
        );

        $buttons[] = MessageButtons::defaultActions(
            textResolver: $this->textResolver,
            messenger: $messenger,
            lang: $lang,
            backBtn: true,
            skipBtn: $isEdit,
            cancelBtn:true
        );

        return $buttons;
    }

    private function isEditMode(BotContext $context): bool
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        return in_array(
            $data['subType'] ?? null,
            config('steps.tasks'),
            true
        );
    }
}
