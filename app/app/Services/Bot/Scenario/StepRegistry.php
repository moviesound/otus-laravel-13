<?php

namespace App\Services\Bot\Scenario;

use App\Contracts\Bot\Scenario\StepRegistryInterface;
use App\Contracts\Bot\Scenario\Steps\StepInterface;

final class StepRegistry implements StepRegistryInterface
{
    public function __construct(
        private readonly array $steps
    ) {
    }

    public function get(string $step): StepInterface
    {
        if (!isset($this->steps[$step])) {
            throw new \RuntimeException("Unknown step [$step]");
        }

        return app($this->steps[$step]);
    }
}
