<?php

namespace App\Contracts\Bot\Scenario\Middleware\Cases;

use App\Services\Bot\Contexts\BotContext;

interface PoliticsMiddlewareInterface
{
    public function handle(BotContext $context): void;
}
