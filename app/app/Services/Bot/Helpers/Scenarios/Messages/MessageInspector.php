<?php

namespace App\Services\Bot\Helpers\Scenarios\Messages;

final class MessageInspector
{
    public static function isInteger(
        string|int|null $message
    ): bool {
        return isset($message)
            && preg_match('/^-?\d+$/u', (string) $message);
    }

    public static function isFloat(
        string|int|float|null $message
    ): bool {
        return isset($message)
            && preg_match(
                '/^-?\d+(\.\d+)?$/u',
                (string) $message
            );
    }

    public static function isGender(
        ?string $message
    ): bool {
        if ($message === null) {
            return false;
        }

        return in_array(
            mb_strtolower(trim($message)),
            config('messages.genders', []),
            true
        );
    }

    public static function isCallbackAction(
        ?string $message,
        array $actions
    ): bool {
        return $message !== null
            && in_array($message, $actions, true);
    }

    public static function isOneOf(
        ?string $message,
        array $values
    ): bool {
        return $message !== null
            && in_array($message, $values, true);
    }
}
