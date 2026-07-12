<?php

namespace App\Services\Bot\Helpers\Scenarios\Messages;

use App\Enums\Bot\MessageIntent;

final readonly class MessageIntentResolver
{
    public function __invoke(?string $message): MessageIntent
    {
        if ($message === null) {
            return MessageIntent::Unknown;
        }

        if (in_array(
            $message,
            config('messages.deny', []),
            true
        )) {
            return MessageIntent::Deny;
        }

        if (in_array(
            $message,
            config('messages.back', []),
            true
        )) {
            return MessageIntent::Back;
        }

        if (in_array(
            $message,
            config('messages.continue', []),
            true
        )) {
            return MessageIntent::Continue;
        }

        if (in_array(
            $message,
            config('messages.stopAlgo', []),
            true
        )) {
            return MessageIntent::Stop;
        }

        return match ($message) {
            'yes'    => MessageIntent::Yes,
            'no'     => MessageIntent::No,
            'edit'   => MessageIntent::Edit,
            'delete' => MessageIntent::Delete,
            'add'    => MessageIntent::Add,
            default  => MessageIntent::Unknown,
        };
    }
}
