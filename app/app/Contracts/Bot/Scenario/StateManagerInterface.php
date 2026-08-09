<?php

namespace App\Contracts\Bot\Scenario;

use App\Services\Bot\Contexts\BotContext;

interface StateManagerInterface
{
    public function goto(
        BotContext $context,
        string $scenario,
        string $step,
        ?array $data = null,
        ?string $additionalInfo = null
    ): void;

    public function clear(BotContext $context): void;
}
