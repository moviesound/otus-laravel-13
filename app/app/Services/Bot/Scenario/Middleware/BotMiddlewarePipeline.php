<?php

namespace App\Services\Bot\Scenario\Middleware;

use App\Contracts\Bot\Scenario\Middleware\BotMiddlewareHandlerInterface;
use App\Contracts\Bot\Scenario\Middleware\Cases\PoliticsMiddlewareInterface;
use App\Services\Bot\BotContext;

/**
 * Applies global business rules before routing execution to a scenario.
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
final class BotMiddlewarePipeline implements BotMiddlewareHandlerInterface
{
    public function __construct(
        private PoliticsMiddlewareInterface $politics,
        //private TariffMiddleware $tariff,
    ) {}

    public function handle(BotContext $context): void
    {
        $this->politics->handle($context);
    }
}
