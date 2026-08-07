<?php

namespace App\Contracts\Bot\Scenario\Middleware;

use App\Services\Bot\Contexts\BotContext;

/**
 * Executes global bot middleware before scenario routing.
 *
 * Middleware may:
 * - redirect user to another scenario;
 * - modify current bot context;
 * - enforce global business rules;
 * - block access to scenarios.
 *
 * Examples:
 * - politics agreement check;
 * - active tariff validation;
 * - onboarding completion check.
 */
interface BotMiddlewareHandlerInterface
{
    public function handle(BotContext $context): void;
}
