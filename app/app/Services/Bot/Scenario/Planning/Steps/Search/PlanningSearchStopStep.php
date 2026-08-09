<?php

namespace App\Services\Bot\Scenario\Planning\Steps\Search;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Errors\WrongDataUseButtonsMessage;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Scenario\StepResultFactory;

final class PlanningSearchStopStep implements StepInterface
{
    public const STEP_KEY = 'planningSearchStop';

    public function __construct(
        private readonly MessengerTextResolver $textResolver,
        private readonly WrongDataUseButtonsMessage $wrongDataUseButtonsMessage,
        private readonly StepRepositoryInterface $stepRepo,
    ) {
    }

    public static function stepKey(): string
    {
        return self::STEP_KEY;
    }

    public function handle(BotContext $context): StepResultDTO
    {
        return StepResultFactory::repeat(
            $this->wrongDataUseButtonsMessage->get($context)
        );
    }

    public function show(BotContext $context, ?string $error = null): void
    {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        $context->messenger?->sendMessage(
            $this->textResolver->get(
                'planning_search_finished',
                $messenger,
                $lang
            ),
            [],
            false,
            true
        );

        $this->stepRepo->clear($context->userSocialId);
    }
}
