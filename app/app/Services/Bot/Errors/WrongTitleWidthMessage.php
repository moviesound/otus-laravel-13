<?php

namespace App\Services\Bot\Errors;

use App\Services\Bot\BotContext;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;
use App\Services\Bot\Messengers\MessengerTextResolver;

class WrongTitleWidthMessage
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
            'title_width_must_be_250',
            $messenger,
            $lang
        );
    }
}
