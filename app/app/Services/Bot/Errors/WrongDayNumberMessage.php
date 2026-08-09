<?php

namespace App\Services\Bot\Errors;

use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;

class WrongDayNumberMessage
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
    )
    {
    }

    public function get(
        BotContext $context
    ): string {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        return $this->textResolver->get(
            'wrong_day_number',
            $messenger,
            $lang
        );
    }
}
