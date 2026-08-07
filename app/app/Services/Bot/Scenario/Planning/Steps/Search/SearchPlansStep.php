<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Search;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Enums\Bot\MessageIntent;
use App\Repositories\Bot\PlanningRepository\SearchPlanningRepository;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageIntentResolver;
use App\Services\Bot\Helpers\Scenarios\Plans\PlanFormatter;
use App\Services\Bot\Scenario\StepResultFactory;

final class SearchPlansStep implements StepInterface
{
    public const STEP_KEY = 'searchPlans';
    private const LIMIT = 30;

    public function __construct(
        private readonly SearchPlanningRepository $searchPlanningRepository,
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
        $message = trim(
            $context->messageDTO->value()
        );

        $intent = ($this->intentResolver)($message);

        return match ($intent) {
            MessageIntent::Stop,
            MessageIntent::Back
            => StepResultFactory::switch(
                FindPlansStep::STEP_KEY
            ),

            default
            => StepResultFactory::switch(
                self::STEP_KEY,
                [
                    'query' => $message
                ]
            ),
        };
    }

    public function show(
        BotContext $context,
        ?string $error = null
    ): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = $this->getData($context);

        $query = $data['query'] ?? '';

        $plans = $this->searchPlanningRepository
            ->searchTasksEventsByScore(
                $context->userDTO->id,
                $query,
                self::LIMIT
            );

        $buttons = [
            MessageButtons::defaultActions(
                textResolver: $this->textResolver,
                messenger: $messenger,
                lang: $lang,
                backBtn: true,
                cancelBtn: true,
            )
        ];

        if (empty($plans)) {
            $context->messenger?->sendMessage(
                $this->textResolver->get(
                    'no_tasks_found',
                    $messenger,
                    $lang
                ),
                $buttons
            );

            return;
        }

        $answer = $this->textResolver->get(
            'tasks_show_search',
            $messenger,
            $lang,
            [
                'tasks' => PlanFormatter::nums($plans, $context->userDTO->timezone),
            ]
        );

        $context->messenger?->sendMessage(
            $context->messenger->format(
                '',
                $answer,
                $error
            ),
            $buttons
        );
    }


    private function getData(BotContext $context): array
    {
        if (!$context->scenarioDTO->data) {
            return [];
        }

        return json_decode(
            $context->scenarioDTO->data,
            true
        ) ?? [];
    }
}
