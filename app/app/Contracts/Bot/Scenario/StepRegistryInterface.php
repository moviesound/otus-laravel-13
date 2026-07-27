<?php

namespace App\Contracts\Bot\Scenario;

use App\Contracts\Bot\Scenario\Steps\StepInterface;

interface StepRegistryInterface
{
    public function get(string $step): StepInterface;
}
