<?php

namespace App\Services\Bot\Scenario\Planning\Steps;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Repositories\Bot\PlanningRepository\SearchPlanningRepository;
use App\Services\Bot\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Messengers\MessengerTextResolver;
use App\Services\Bot\Scenario\StepResultFactory;

final class SelectPlanTypeStep implements StepInterface
{
    public function __construct(
        private readonly MessageIntentResolver    $intentResolver,
        private readonly MessengerTextResolver    $textResolver,
        private readonly SearchPlanningRepository $searchPlanningRepository,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
    )
    {
    }

    public const STEP_KEY = 'selectPlanType';

    public static function stepKey(): string
    {
        return static::STEP_KEY
            ?? throw new \LogicException('Step KEY not defined');
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $answer = $this->textResolver->get(
            'task_action_choose',
            $messenger,
            $lang
        );

        $answer = $context->messenger->format(
            '',
            $answer,
            $error
        );

        $buttons = $this->getButtons($context, $messenger, $lang);

        $context->messenger?->sendMessage(
            $answer,
            $buttons
        );
    }

    private function getButtons(BotContext $context, ?string $messenger, string $lang): array
    {
        $buttons = [
            [
                [
                    'text' => $this->textResolver->get(
                        'create_task_btn',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'addTaskBtn',
                ],
                [
                    'text' => $this->textResolver->get(
                        'create_event_btn',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'addEventBtn',
                ],
            ]
        ];

        $row = [];
        if (!$this->hasTasks($context)) {
            $row[] = [
                'text' => $this->textResolver->get(
                    'find_task',
                    $messenger,
                    $lang
                ),
                'callback_data' => 'findPlans',
            ];
        }
        $row[] = [
            'text' => $this->textResolver->get(
                'cancel',
                $messenger,
                $lang
            ),
            'callback_data' => 'cancel',
        ];

        $buttons[] = $row;

        return $buttons;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = $context->messageDTO->value();
        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Stop
            => StepResultFactory::switch(PlanningDoneStep::STEP_KEY),

            default
            => $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        return match ($message) {
            '/task', '/plan', '/event', '/remind'
            => StepResultFactory::repeat(),

            'addTaskBtn', 'createTask', 'task'
            => StepResultFactory::switch(SelectTaskTypeStep::STEP_KEY, ['type' => 'task']),

            'addEventBtn', 'createEvent', 'event'
            => StepResultFactory::switch(SelectEventTypeStep::STEP_KEY, ['type' => 'event']),

            'findPlans'
            => StepResultFactory::switch(FindPlansStep::STEP_KEY),

            default
            => StepResultFactory::repeat(
                $this->wrongDataUseButtonsMessage->get($context)
            ),
        };
    }

    private function hasTasks(BotContext $context): bool
    {
        return count($this->searchPlanningRepository->searchAllUsersTasksEvents($context->userDTO->id, 1, 0)) > 0;
    }
}
