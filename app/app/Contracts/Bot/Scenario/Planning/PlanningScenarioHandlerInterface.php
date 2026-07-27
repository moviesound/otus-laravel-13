<?php

namespace App\Contracts\Bot\Scenario\Planning;

use App\Services\Bot\BotContext;

interface PlanningScenarioHandlerInterface
{
    public function handle(
        BotContext $context
    ): void;
}
