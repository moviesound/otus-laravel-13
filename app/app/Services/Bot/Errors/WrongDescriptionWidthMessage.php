<?php

namespace App\Services\Bot\Errors;

use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;

class WrongDescriptionWidthMessage
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
    )
    {
    }

    public function get(
        BotContext $context,
    ): string {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        return $this->textResolver->get(
            'description_width_must_be_4000',
            $messenger,
            $lang
        );
    }
}
