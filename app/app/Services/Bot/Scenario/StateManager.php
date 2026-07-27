<?php

namespace App\Services\Bot\Scenario;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Contracts\Bot\Scenario\StateManagerInterface;
use App\DTO\Bot\State\StepStateDTO;
use App\Services\Bot\BotContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Helpers\Scenarios\ScenarioHelper;


class StateManager implements StateManagerInterface
{
    public function __construct(
        private StepRepositoryInterface $steps
    ) {}

    public function goto(
        BotContext $context,
        string $scenario,
        string $step,
        ?array $data = null,
        ?string $additionalInfo = null
    ): void {
        $existingData = [];

        if (!empty($context->scenarioDTO->data)) {
            $existingData = ScenarioHelper::dataNormalizer($context->scenarioDTO->data);
        }

        $mergedData = array_merge(
            $existingData,
            $data ?? []
        );

        $context->scenarioDTO = $dto = new StepStateDTO(
            userSocialId: $context->scenarioDTO->userSocialId,
            scenario: $scenario,
            step: $step,
            message: $context->messageDTO->value(),
            data: json_encode($mergedData),
            additionalInfo: $additionalInfo ?: $context->scenarioDTO->additionalInfo,
            commonEntityId: null,
        );

        $this->steps->save($dto);
    }

    public function clear(BotContext $context): void
    {
        $this->steps->clear(
            $context->scenarioDTO->userSocialId
        );
    }
}
