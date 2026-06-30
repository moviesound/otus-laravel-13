<?php

namespace App\Contracts\Bot\Scenario;

use App\Services\Bot\BotContext;

interface ScenarioRouterInterface
{
    public function route(BotContext $context): void;
}
