<?php

namespace App\Contracts\Bot\Messengers;

use App\Services\Bot\Contexts\BotContext;

/**
 * Coordinates the bot message processing pipeline.
 *
 * This service does not contain business logic.
 * Its responsibility is orchestration only:
 * - middleware execution
 * - scenario routing
 */
interface MessageHandlerInterface
{
    public function handle(BotContext $context): void;
}
