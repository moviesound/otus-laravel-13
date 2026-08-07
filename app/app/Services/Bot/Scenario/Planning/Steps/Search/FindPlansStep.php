<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Search;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Scenario\StepResultFactory;

final class FindPlansStep implements StepInterface
{
    public const STEP_KEY = 'findPlans';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly MessageIntentResolver $intentResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
    ) {}

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        $message = trim($context->messageDTO->value());

        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Stop
            => StepResultFactory::switch(
                PlanningSearchStopStep::STEP_KEY
            ),

            default
            => $this->handleMessage($context, $message),
        };
    }

    private function handleMessage(BotContext $context, string $message): StepResultDTO
    {
        return match ($message) {

            'showAllPlans'
            => StepResultFactory::switch(
                ShowPlansStep::STEP_KEY,
                [
                    'page' => 0,
                ]
            ),

            default
            => mb_strlen($message) < 3
                ? StepResultFactory::repeat(
                    $this->wrongDataUseButtonsMessage->get($context)
                )
                : StepResultFactory::switch(
                    SearchPlansStep::STEP_KEY,
                    [
                        'query' => $message,
                    ]
                ),
        };
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $body = $this->textResolver->get(
            'task_enter_search',
            $messenger,
            $lang,
            ['amount' => config('steps.search_tasks_amount')]
        );

        $context->messenger?->sendMessage(
            $context->messenger->format('', $body, $error),
            $this->buildButtons($messenger, $lang)
        );
    }

    private function buildButtons(string $messenger, string $lang): array
    {
        return [
            [
                [
                    'text' => $this->textResolver->get(
                        'show_all_tasks',
                        $messenger,
                        $lang
                    ),
                    'callback_data' => 'showAllPlans',
                ],
            ],
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                cancelBtn: true,
            ),
        ];
    }
}
