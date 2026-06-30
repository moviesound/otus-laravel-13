<?php

namespace App\Contracts\Bot\Scenario;

use App\DTO\Bot\State\StepStateDTO;

interface ScenarioResolverInterface
{
    public function resolve(
        ?StepStateDTO $state,
        int           $userSocialId,
        string        $message
    ): StepStateDTO;
}
