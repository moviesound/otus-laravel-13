<?php

namespace App\Services\Bot;

use App\Contracts\Bot\Messengers\MessageHandlerInterface;
use App\Contracts\Bot\Scenario\Middleware\BotMiddlewareHandlerInterface;
use App\Contracts\Bot\Scenario\ScenarioRouterInterface;
use App\Services\Bot\Contexts\BotContext;

//use App\Services\Bot\TelegramWebhook\PromoService;

/**
 * Pipeline orchestrator.
 * This service does not contain business logic.
 * Its responsibility is orchestration only:
 * - middleware execution
 * - scenario routing
 */
class MessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private BotMiddlewareHandlerInterface $middleware,
        private ScenarioRouterInterface $router,
    ) {}

    public function handle(BotContext $context): void
    {
        $this->middleware->handle($context);

        $this->router->route($context);

        if ($context->shouldDeleteUserMessages ?? true) {
            $context->messenger?->deleteUserMessages();
        }
    }
}
