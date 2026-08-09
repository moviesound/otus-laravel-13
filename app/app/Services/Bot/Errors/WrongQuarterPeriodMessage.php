<?php

namespace App\Services\Bot\Errors;

use App\Services\Bot\Contexts\BotContext;
use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageContext;

class WrongQuarterPeriodMessage
{
    public function __construct(
        private readonly MessengerTextResolver $textResolver,
    )
    {
    }

    public function get(
        BotContext $context,
        string $monthStart,
        string $monthEnd,
        string $dayStart,
        string $dayEnd,
    ): string {
        [$messenger, $lang] = MessageContext::getMessengerAndLang($context);

        return $this->textResolver->get(
            'wrong_quarter_period_message',
            $messenger,
            $lang,
            [
                'month_start' => $monthStart,
                'month_end' => $monthEnd,
                'day_start' => $dayStart,
                'day_end' => $dayEnd,
            ]
        );
    }
}
