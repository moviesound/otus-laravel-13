<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDescriptionWidthMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\Planning\Steps\Tags\AddPlanningTagsStep;
use App\Services\Bot\Scenario\StepResultFactory;

class AddPlanningDescriptionStep implements StepInterface
{
    public function __construct(
        private readonly MessageIntentResolver $intentResolver,
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongDescriptionWidthMessage $wrongDescriptionWidthMessage,
    )
    {
    }

    public const STEP_KEY = 'addPlanningDescription';

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
            StepResultFactory::switch(AddPlanningTitleStep::STEP_KEY),

            MessageIntent::Continue =>
            StepResultFactory::switch(AddPlanningTagsStep::STEP_KEY),

            default =>
            $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(
        BotContext $context,
        string $message
    ): StepResultDTO {

        if (mb_strlen($message) > 4000) {
            return StepResultFactory::repeat(
                $this->wrongDescriptionWidthMessage->get($context)
            );
        }

        return StepResultFactory::switch(
            AddPlanningTagsStep::STEP_KEY,
            [
                'description' => $message,
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
            $lang
        );

        $context->messenger?->sendMessage(
            $answer,
            $buttons
        );
    }

    private function getAnswer(
        BotContext $context,
        string $messenger,
        string $lang,
        ?string $error,
        bool $isEdit
    ): string {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        $type = $data['type'];

        $headerKey = ScenarioHelper::headerKey($context);

        if ($isEdit) {

            $textKey = $type === 'task'
                ? 'task_change_description'
                : 'event_change_description';

            $replace = [
                'description' => $data['description'],
            ];

        } else {

            $textKey = $type === 'task'
                ? 'task_add_description'
                : 'event_add_description';

            $replace = [];
        }

        return $context->messenger->format(
            $this->textResolver->header($messenger, $lang, $headerKey, 'task_step_description'),
            $this->textResolver->get(
                $textKey,
                $messenger,
                $lang,
                $replace
            ),
            $error
        );
    }

    private function getButtons(
        string $messenger,
        string $lang
    ): array {

        return [
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                skipBtn: true,
                cancelBtn: true,
            )
        ];
    }

    private function isEditMode(
        BotContext $context
    ): bool {
        $data = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        return !empty($data['description'] ?? null);
    }
}
