<?php

namespace App\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\ScenarioResolverInterface;
use App\DTO\Bot\State\StepStateDTO;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;

class ScenarioResolver implements ScenarioResolverInterface
{
    public function resolve(
        ?StepStateDTO $state,
        int $userSocialId,
        string $message
    ): StepStateDTO {
        if ($state !== null) {
            return $state;
        }

        return $this->resolveStart($userSocialId, $message);
    }

    private function resolveStart(
        int $userSocialId,
        string $message
    ): StepStateDTO {
        return match ($message) {
            '/start' => new StepStateDTO(
                userSocialId: $userSocialId,
                scenario: 'onBoarding',
                step: 'askName',
                message: null,
                data: null,
                additionalInfo: null,
                commonEntityId: null,
            ),

            '/task', '/event', '/plan' => new StepStateDTO(
                userSocialId: $userSocialId,
                scenario: 'onPlanning',
                step: SelectPlanTypeStep::STEP_KEY,
                message: null,
                data: null,
                additionalInfo: null,
                commonEntityId: null,
            ),

            default => new StepStateDTO(
                userSocialId: $userSocialId,
                scenario: 'unknown',
                step: 'unknown',
                message: null,
                data: null,
                additionalInfo: null,
                commonEntityId: null,
            ),
        };
    }
}
