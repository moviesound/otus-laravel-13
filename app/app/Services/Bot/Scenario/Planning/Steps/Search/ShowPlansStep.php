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

final class ShowPlansStep implements StepInterface
{
    public const STEP_KEY = 'showPlans';
    private const AMOUNT = 10;

    public function __construct(
        private readonly SearchPlanningRepository $searchPlanningRepository,
        private readonly MessengerTextResolver    $textResolver,
        private readonly MessageIntentResolver    $intentResolver,
    )
    {
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
                FindPlansStep::STEP_KEY,
                [
                    'page' => 0
                ]
            ),

            default
            => $this->handleMessage(
                $context,
                $message
            ),
        };
    }

    private function handleMessage(
        BotContext $context,
        string     $message
    ): StepResultDTO
    {
        $data = $this->getData($context);
        $page = $data['page'] ?? 0;

        return match ($message) {
            'next'
            => StepResultFactory::switch(
                self::STEP_KEY,
                [
                    'page' => $page + 1
                ]
            ),

            'previous'
            => StepResultFactory::switch(
                self::STEP_KEY,
                [
                    'page' => max(0, $page - 1)
                ]
            ),

            default
            => StepResultFactory::repeat(),
        };
    }

    public function show(
        BotContext $context,
        ?string    $error = null
    ): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $data = $this->getData($context);
        $page = $data['page'] ?? 0;
        $offset = $page * self::AMOUNT;

        $plans = $this->searchPlanningRepository
            ->searchAllUsersTasksEvents(
                $context->userDTO->id,
                self::AMOUNT,
                $offset
            );

        if (empty($plans)) {
            $buttons = [
                MessageButtons::defaultActions(
                    textResolver: $this->textResolver,
                    messenger: $messenger,
                    lang: $lang,
                    backBtn: true
                )
            ];
            if ($page > 0) {
                $context->messenger?->sendMessage(
                    $this->textResolver->get(
                        'no_more_tasks',
                        $messenger,
                        $lang
                    ),
                    $buttons
                );
                return;
            }

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

        $count = $this->searchPlanningRepository
            ->countAllUsersTasksEvents(
                $context->userDTO->id
            );

        $maxPage = (int)ceil(
            $count / self::AMOUNT
        );

        $header = '';

        $answer = $this->textResolver->get(
            'tasks_show',
            $messenger,
            $lang,
            [
                'page' => (string)($page + 1),
                'maxpage' => (string)$maxPage,
                'tasks' => PlanFormatter::nums($plans, $context->userDTO->timezone, $offset),
            ]
        );

        $buttons = $this->buildButtons(
            $page,
            $maxPage,
            $messenger,
            $lang
        );

        $context->messenger?->sendMessage(
            $context->messenger->format(
                $header,
                $answer,
                $error
            ),
            $buttons
        );
    }

    private function buildButtons(
        int    $page,
        int    $maxPage,
        string $messenger,
        string $lang
    ): array
    {
        $buttons = [];
        $row = [];

        if ($page > 0) {
            $row[] = [
                'text' => $this->textResolver->get(
                    'previous_btn',
                    $messenger,
                    $lang
                ),
                'callback_data' => 'previous',
            ];
        }

        if ($page + 1 < $maxPage) {
            $row[] = [
                'text' => $this->textResolver->get(
                    'next_btn',
                    $messenger,
                    $lang
                ),
                'callback_data' => 'next',
            ];
        }

        if (!empty($row)) {
            $buttons[] = $row;
        }

        $buttons[] = MessageButtons::defaultActions(
            textResolver: $this->textResolver,
            messenger: $messenger,
            lang: $lang,
            backBtn: true,
            cancelBtn: true,
        );

        return $buttons;
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
