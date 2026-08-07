<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongTitleWidthMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Scenario\StepResultFactory;

class AddPlanningTitleStep implements StepInterface
{
    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongTitleWidthMessage $wrongTitleWidthMessage,
    )
    {
    }

    public const STEP_KEY = 'addPlanningTitle';

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
            StepResultFactory::switch(
                $this->backStep($context)
            ),

            MessageIntent::Continue =>
            StepResultFactory::switch(AddPlanningDescriptionStep::STEP_KEY),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        if (mb_strlen($message) > 250) {
            return StepResultFactory::repeat(
                $this->wrongTitleWidthMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            AddPlanningDescriptionStep::STEP_KEY,
            [
                'title' => $message,
            ]
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $isEdit = $this->isEditMode($context);

        $answer = $this->getAnswer(
            $context,
            $messenger,
            $lang,
            $error,
            $isEdit
        );

        $buttons = $this->getButtons(
            $messenger,
            $lang,
            $isEdit
        );

        $context->messenger?->sendMessage(
            $answer,
            $buttons
        );
    }

    private function getAnswer(
        BotContext $context,
        string     $messenger,
        string     $lang,
        ?string    $error,
        bool       $isEdit
    ): string
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $type = $data['type'];

        $headerKey = ScenarioHelper::headerKey($context);

        if ($isEdit) {
            $textKey = $type === 'task'
                ? 'task_change_title'
                : 'event_change_title';

            $replace = [
                'title' => $data['title'],
            ];

        } else {

            $textKey = $type === 'task'
                ? 'task_add_title'
                : 'event_add_title';

            $replace = [];
        }
        return $context->messenger->format(
            $this->textResolver->header($messenger, $lang, $headerKey, 'task_step_name'),
            $this->textResolver->get(
                $textKey,
                $messenger,
                $lang,
                $replace
            ),
            $error
        );
    }

    private function getButtons(string $messenger, string $lang, bool $isEdit): array
    {
        return [
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                skipBtn: $isEdit,
                cancelBtn: true,
            )
        ];
    }

    private function isEditMode(BotContext $context): bool
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        return !empty(
            $data['title'] ?? null
        );
    }

    private function backStep(BotContext $context): string
    {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        return match (
            $data['type'] ?? null
        ) {
            'task' => SelectTaskTypeStep::STEP_KEY,
            'event' => SelectEventTypeStep::STEP_KEY,

            default => SelectPlanTypeStep::STEP_KEY,
        };
    }
}
