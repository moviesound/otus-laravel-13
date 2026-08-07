<?php

namespace App\Contracts\Bot\Scenario\Politics;

use App\Services\Bot\Contexts\BotContext;

interface PoliticsScenarioHandlerInterface
{
    public function handle(
        BotContext $context
    ): void;
}
