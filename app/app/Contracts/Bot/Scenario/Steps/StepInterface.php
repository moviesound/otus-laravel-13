<?php

namespace App\Contracts\Bot\Scenario\Steps;

use App\DTO\Bot\Scenarios\StepResultDTO;
use App\Services\Bot\Contexts\BotContext;

interface StepInterface
{
    public static function stepKey(): string;

    public function handle(
        BotContext $context
    ): StepResultDTO;

    public function show(
        BotContext $context,
        ?string $error = null
    ): void;
}
