<?php

namespace App\Contracts\Bot\Scenario\Planning;

use App\Contracts\Bot\Scenario\Steps\StepInterface;

interface PlanningStepRegistryInterface
{
    public function get(string $step): StepInterface;
}
