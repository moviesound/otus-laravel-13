<?php

namespace App\Contracts\Bot\Scenario;

use App\Services\Bot\BotContext;

interface StateManagerInterface
{
    public function goto(
        BotContext $context,
        string $scenario,
        string $step,
        ?array $data = null,
    ): void;

    public function clear(BotContext $context): void;
}
