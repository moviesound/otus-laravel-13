<?php

namespace App\Contracts\Bot\Scenario;

use App\Contracts\Bot\Scenario\Steps\StepInterface;
use App\Services\Bot\BotContext;

interface StepExecutorInterface
{
    public function execute(StepInterface $step, BotContext $context): void;
}
