<?php

namespace App\Contracts\Bot\Scenario;

use App\Services\Bot\Contexts\BotContext;

interface ScenarioRouterInterface
{
    public function route(BotContext $context): void;
}
